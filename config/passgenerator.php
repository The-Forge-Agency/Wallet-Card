<?php

return [
    // Chemin vers le certificat Pass Type ID exporté en PKCS#12 (.p12)
    'certificate_store_path' => env('CERTIFICATE_PATH', storage_path('app/certs/pass.p12')),

    // Mot de passe du .p12
    'certificate_store_password' => env('CERTIFICATE_PASS', ''),

    // Certificat intermédiaire Apple WWDR au format PEM
    'wwdr_certificate_path' => env('WWDR_CERTIFICATE', storage_path('app/certs/wwdr.pem')),
];
