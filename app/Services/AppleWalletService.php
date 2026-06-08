<?php

namespace App\Services;

use App\Exceptions\WalletPassUnavailableException;
use App\Models\Card;
use App\Support\Color;
use Thenextweb\PassGenerator;

class AppleWalletService
{
    public function __construct(private readonly ImageProcessor $images) {}

    /**
     * Tous les prérequis sont-ils réunis pour signer un .pkpass ?
     */
    public function isConfigured(): bool
    {
        $certPath = (string) config('passgenerator.certificate_store_path');
        $wwdrPath = (string) config('passgenerator.wwdr_certificate_path');

        return config('walletcard.pass_type_identifier')
            && config('walletcard.team_identifier')
            && is_file($certPath)
            && is_file($wwdrPath);
    }

    /**
     * Construit et signe le .pkpass. Retourne le contenu binaire.
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

        $pass = new PassGenerator($card->code, true);
        $pass->setPassDefinition($this->definition($card));

        $base = public_path('images/pass');
        foreach (['icon.png', 'icon@2x.png', 'icon@3x.png', 'logo.png'] as $asset) {
            $pass->addAsset($base.'/'.$asset);
        }

        $thumbnail = $this->thumbnailFor($card);
        if ($thumbnail !== null) {
            $pass->addAsset($thumbnail);
        }

        try {
            return $pass->create();
        } finally {
            if ($thumbnail !== null && is_file($thumbnail)) {
                @unlink($thumbnail);
            }
        }
    }

    private function thumbnailFor(Card $card): ?string
    {
        if (! $card->image_path) {
            return null;
        }

        $dir = storage_path('app/pass-tmp');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

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
