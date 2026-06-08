<?php

return [
    // --- Apple Wallet ---
    'pass_type_identifier' => env('PASS_TYPE_IDENTIFIER', ''),
    'team_identifier' => env('PASS_TEAM_IDENTIFIER', ''),
    'organization_name' => env('PASS_ORGANIZATION_NAME', 'WalletCard'),

    // --- Google Wallet ---
    'google' => [
        // Issuer ID obtenu sur la Google Pay & Wallet Console
        'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID', ''),

        // Chemin du JSON de la clé du compte de service
        'service_account' => env('GOOGLE_WALLET_SERVICE_ACCOUNT', storage_path('app/certs/google-wallet.json')),

        // Suffixe de la classe generic (un seul template pour toute l'app)
        'class_suffix' => env('GOOGLE_WALLET_CLASS_SUFFIX', 'walletcard'),
    ],
];
