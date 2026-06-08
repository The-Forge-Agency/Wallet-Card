<?php

return [
    // Certificat de signature : PEM (recommandé) ou .p12.
    // Par défaut storage/app/certs/pass.pem — robuste en local comme sur Forge.
    'certificate_store_path' => env('CERTIFICATE_PATH') ?: storage_path('app/certs/pass.pem'),

    // Mot de passe (vide pour un PEM non chiffré)
    'certificate_store_password' => env('CERTIFICATE_PASS', ''),

    // Certificat intermédiaire Apple WWDR au format PEM
    'wwdr_certificate_path' => env('WWDR_CERTIFICATE') ?: storage_path('app/certs/wwdr.pem'),
];
