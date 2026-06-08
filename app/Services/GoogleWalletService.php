<?php

namespace App\Services;

use App\Exceptions\WalletPassUnavailableException;
use App\Models\Card;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Storage;

class GoogleWalletService
{
    /**
     * Tous les prérequis Google Wallet sont-ils réunis ?
     */
    public function isConfigured(): bool
    {
        return (bool) config('walletcard.google.issuer_id')
            && is_file((string) config('walletcard.google.service_account'))
            && $this->credentials() !== null;
    }

    /**
     * URL "Add to Google Wallet" (JWT signé contenant la classe + l'objet).
     *
     * @throws WalletPassUnavailableException si non configuré
     */
    public function saveUrl(Card $card): string
    {
        $creds = $this->credentials();

        if (! config('walletcard.google.issuer_id') || $creds === null) {
            throw new WalletPassUnavailableException('Google Wallet non configuré.');
        }

        $payload = [
            'iss' => $creds['client_email'],
            'aud' => 'google',
            'typ' => 'savetowallet',
            'iat' => time(),
            'origins' => [config('app.url')],
            'payload' => [
                'genericClasses' => [$this->classDefinition()],
                'genericObjects' => [$this->objectDefinition($card)],
            ],
        ];

        $jwt = JWT::encode($payload, $creds['private_key'], 'RS256');

        return 'https://pay.google.com/gp/v/save/'.$jwt;
    }

    private function issuerId(): string
    {
        return (string) config('walletcard.google.issuer_id');
    }

    private function classId(): string
    {
        return $this->issuerId().'.'.config('walletcard.google.class_suffix');
    }

    private function objectId(Card $card): string
    {
        return $this->issuerId().'.'.$card->code;
    }

    /**
     * @return array<string, mixed>
     */
    private function classDefinition(): array
    {
        return [
            'id' => $this->classId(),
            'classTemplateInfo' => [
                'cardTemplateOverride' => [
                    'cardRowTemplateInfos' => [],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function objectDefinition(Card $card): array
    {
        $object = [
            'id' => $this->objectId($card),
            'classId' => $this->classId(),
            'state' => 'ACTIVE',
            'genericType' => 'GENERIC_TYPE_UNSPECIFIED',
            'hexBackgroundColor' => $card->bg_color,
            'cardTitle' => [
                'defaultValue' => ['language' => 'fr', 'value' => config('walletcard.organization_name')],
            ],
            'header' => [
                'defaultValue' => ['language' => 'fr', 'value' => $card->displayTitle()],
            ],
            'logo' => [
                'sourceUri' => ['uri' => asset('images/icon-512.png')],
                'contentDescription' => ['defaultValue' => ['language' => 'fr', 'value' => 'WalletCard']],
            ],
        ];

        if ($card->subtitle) {
            $object['subheader'] = [
                'defaultValue' => ['language' => 'fr', 'value' => $card->subtitle],
            ];
        }

        if ($card->hasQr()) {
            $object['barcode'] = [
                'type' => 'QR_CODE',
                'value' => $card->qrPayload(),
            ];
        }

        $textModules = [];
        foreach (array_merge($card->frontFields(), $card->backFields()) as $i => $field) {
            if ($field['label'] === '' && $field['value'] === '') {
                continue;
            }
            $textModules[] = [
                'id' => 'field'.$i,
                'header' => $field['label'] ?: ' ',
                'body' => $field['value'] ?: ' ',
            ];
        }

        if ($textModules !== []) {
            $object['textModulesData'] = $textModules;
        }

        if ($card->image_path) {
            $object['heroImage'] = [
                'sourceUri' => ['uri' => Storage::disk('public')->url($card->image_path)],
            ];
        }

        return $object;
    }

    /**
     * Lit le compte de service. Retourne null si illisible/invalide.
     *
     * @return array{client_email: string, private_key: string}|null
     */
    private function credentials(): ?array
    {
        $path = (string) config('walletcard.google.service_account');

        if (! is_file($path)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($path), true);

        if (! is_array($data) || empty($data['client_email']) || empty($data['private_key'])) {
            return null;
        }

        return [
            'client_email' => $data['client_email'],
            'private_key' => $data['private_key'],
        ];
    }
}
