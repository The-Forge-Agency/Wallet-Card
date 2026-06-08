<?php

namespace App\Services;

use App\Exceptions\WalletPassUnavailableException;
use App\Models\Card;
use App\Support\Color;
use RuntimeException;
use ZipArchive;

class AppleWalletService
{
    public function __construct(private readonly ImageProcessor $images) {}

    /**
     * Tous les prérequis sont-ils réunis pour signer un .pkpass ?
     */
    public function isConfigured(): bool
    {
        return (bool) config('walletcard.pass_type_identifier')
            && config('walletcard.team_identifier')
            && is_file((string) config('passgenerator.certificate_store_path'))
            && is_file((string) config('passgenerator.wwdr_certificate_path'));
    }

    /**
     * Construit et signe le .pkpass nativement. Retourne le contenu binaire.
     *
     * @throws WalletPassUnavailableException si les certificats ne sont pas configurés
     */
    public function generate(Card $card): string
    {
        if (! $this->isConfigured()) {
            throw new WalletPassUnavailableException(
                'La génération Apple Wallet n\'est pas encore configurée (certificats manquants).'
            );
        }

        [$certPem, $keyPem] = $this->readCertificate();

        // 1. Fichiers du pass (pass.json + assets)
        $files = [
            'pass.json' => json_encode($this->definition($card), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];
        foreach (['icon.png', 'icon@2x.png', 'icon@3x.png', 'logo.png'] as $asset) {
            $files[$asset] = (string) file_get_contents(public_path('images/pass/'.$asset));
        }
        if ($thumbnail = $this->thumbnailFor($card)) {
            $files['thumbnail.png'] = (string) file_get_contents($thumbnail);
            $files['thumbnail@2x.png'] = $files['thumbnail.png'];
            @unlink($thumbnail);
        }

        // 2. manifest.json = sha1 de chaque fichier
        $manifest = [];
        foreach ($files as $name => $content) {
            $manifest[$name] = sha1($content);
        }
        $files['manifest.json'] = json_encode($manifest, JSON_UNESCAPED_SLASHES);

        // 3. signature détachée PKCS#7 du manifest
        $files['signature'] = $this->sign($files['manifest.json'], $certPem, $keyPem);

        // 4. zip → .pkpass
        return $this->zip($files);
    }

    /**
     * Lit le certificat de signature. Supporte un PEM combiné (cert + clé)
     * ou un .p12. Le PEM est recommandé : OpenSSL 3 refuse les .p12 « legacy »
     * exportés par le Trousseau macOS.
     *
     * @return array{0: string, 1: string} cert PEM, clé privée PEM
     */
    private function readCertificate(): array
    {
        $path = (string) config('passgenerator.certificate_store_path');
        $store = (string) file_get_contents($path);
        $password = (string) config('passgenerator.certificate_store_password');

        // Format PEM (cert + clé dans le même fichier)
        if (str_contains($store, 'BEGIN CERTIFICATE') && str_contains($store, 'PRIVATE KEY')) {
            preg_match('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', $store, $cert);
            preg_match('/-----BEGIN (?:[A-Z0-9 ]*)PRIVATE KEY-----.*?-----END (?:[A-Z0-9 ]*)PRIVATE KEY-----/s', $store, $key);

            if (empty($cert) || empty($key)) {
                throw new RuntimeException('PEM invalide : certificat ou clé privée introuvable.');
            }

            return [$cert[0], $key[0]];
        }

        // Format .p12 / PKCS#12
        $certs = [];
        if (! openssl_pkcs12_read($store, $certs, $password)) {
            throw new RuntimeException(
                'Impossible de lire le .p12. Sur OpenSSL 3, convertis-le en PEM : '.
                'openssl pkcs12 -in pass.p12 -nodes -out pass.pem'
            );
        }

        return [$certs['cert'], $certs['pkey']];
    }

    private function sign(string $manifest, string $certPem, string $keyPem): string
    {
        $dir = $this->tmpDir();
        $manifestPath = $dir.'/manifest.json';
        $signaturePath = $dir.'/signature.p7s';
        file_put_contents($manifestPath, $manifest);

        $signed = openssl_pkcs7_sign(
            $manifestPath,
            $signaturePath,
            $certPem,
            [$keyPem, (string) config('passgenerator.certificate_store_password')],
            [],
            PKCS7_BINARY | PKCS7_DETACHED,
            (string) config('passgenerator.wwdr_certificate_path'),
        );

        if (! $signed) {
            throw new RuntimeException('Échec de la signature du manifest (openssl_pkcs7_sign).');
        }

        // openssl écrit du S/MIME ; on extrait le bloc DER base64.
        $smime = (string) file_get_contents($signaturePath);
        @unlink($manifestPath);
        @unlink($signaturePath);
        @rmdir($dir);

        return $this->smimeToDer($smime);
    }

    /**
     * Convertit la sortie S/MIME d'openssl_pkcs7_sign en signature DER brute.
     */
    private function smimeToDer(string $smime): string
    {
        $parts = preg_split("/\n\s*\n/", $smime) ?: [];

        foreach ($parts as $part) {
            $candidate = base64_decode(trim($part), true);
            // Une structure DER PKCS#7 commence par une SEQUENCE (0x30).
            if ($candidate !== false && $candidate !== '' && ord($candidate[0]) === 0x30) {
                return $candidate;
            }
        }

        throw new RuntimeException('Signature PKCS#7 introuvable dans la sortie S/MIME.');
    }

    /**
     * @param  array<string, string>  $files
     */
    private function zip(array $files): string
    {
        $path = $this->tmpDir().'/pass.pkpass';
        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossible de créer l\'archive .pkpass.');
        }

        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        $binary = (string) file_get_contents($path);
        @unlink($path);
        @rmdir(dirname($path));

        return $binary;
    }

    private function tmpDir(): string
    {
        $dir = storage_path('app/pass-tmp/'.bin2hex(random_bytes(6)));
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return $dir;
    }

    private function thumbnailFor(Card $card): ?string
    {
        if (! $card->image_path) {
            return null;
        }

        $dir = $this->tmpDir();

        return $this->images->makePassThumbnail($card->image_path, $dir.'/thumbnail.png');
    }

    /**
     * @return array<string, mixed>
     */
    private function definition(Card $card): array
    {
        $generic = [
            'primaryFields' => [[
                'key' => 'name',
                'value' => $card->displayTitle(),
            ]],
            'secondaryFields' => [],
            'auxiliaryFields' => [],
            'backFields' => [],
        ];

        if ($card->header_value) {
            $generic['headerFields'][] = [
                'key' => 'header',
                'label' => (string) $card->header_label,
                'value' => $card->header_value,
            ];
        }

        if ($card->subtitle) {
            $generic['secondaryFields'][] = [
                'key' => 'subtitle',
                'label' => '',
                'value' => $card->subtitle,
            ];
        }

        foreach ($card->frontFields() as $i => $field) {
            $generic['auxiliaryFields'][] = [
                'key' => 'front'.$i,
                'label' => $field['label'],
                'value' => $field['value'] ?: ' ',
            ];
        }

        foreach ($card->backFields() as $i => $field) {
            $generic['backFields'][] = [
                'key' => 'back'.$i,
                'label' => $field['label'],
                'value' => $field['value'] ?: ' ',
            ];
        }

        $generic['backFields'][] = [
            'key' => 'made_with',
            'label' => 'Créée avec',
            'value' => 'WalletCard — '.route('cards.show', $card->code),
        ];

        $definition = [
            'formatVersion' => 1,
            'passTypeIdentifier' => config('walletcard.pass_type_identifier'),
            'teamIdentifier' => config('walletcard.team_identifier'),
            'organizationName' => config('walletcard.organization_name'),
            'serialNumber' => $card->code,
            'description' => 'WalletCard — '.$card->displayTitle(),
            'backgroundColor' => Color::hexToRgbString($card->bg_color),
            'foregroundColor' => Color::hexToRgbString($card->text_color),
            'labelColor' => Color::hexToRgbString($card->text_color),
            'generic' => $generic,
        ];

        if ($card->hasQr()) {
            $barcode = [
                'format' => 'PKBarcodeFormatQR',
                'message' => $card->qrPayload(),
                'messageEncoding' => 'iso-8859-1',
            ];
            $definition['barcodes'] = [$barcode];
            $definition['barcode'] = $barcode; // rétrocompat iOS < 9
        }

        return $definition;
    }
}
