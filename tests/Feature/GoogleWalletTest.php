<?php

use App\Models\Card;
use App\Services\GoogleWalletService;

function fakeGoogleServiceAccount(): string
{
    $res = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($res, $privateKey);

    $path = storage_path('app/certs/google-test.json');
    @mkdir(dirname($path), 0775, true);
    file_put_contents($path, json_encode([
        'client_email' => 'test@walletcard.iam.gserviceaccount.com',
        'private_key' => $privateKey,
    ]));

    return $path;
}

afterEach(function () {
    @unlink(storage_path('app/certs/google-test.json'));
});

it('n\'est pas configuré par défaut', function () {
    config()->set('walletcard.google.issuer_id', '');

    expect(app(GoogleWalletService::class)->isConfigured())->toBeFalse();
});

it('génère une URL Add to Google Wallet signée', function () {
    config()->set('walletcard.google.issuer_id', '1234567890');
    config()->set('walletcard.google.service_account', fakeGoogleServiceAccount());

    $card = Card::factory()->create();
    $url = app(GoogleWalletService::class)->saveUrl($card);

    expect($url)->toStartWith('https://pay.google.com/gp/v/save/');

    // Le payload du JWT contient bien l'objet de la carte
    $jwt = str($url)->after('save/')->value();
    [, $body] = explode('.', $jwt);
    $payload = json_decode(base64_decode(strtr($body, '-_', '+/')), true);

    expect($payload['payload']['genericObjects'][0]['id'])->toBe('1234567890.'.$card->code)
        ->and($payload['typ'])->toBe('savetowallet');
});

it('redirige la route google quand configuré', function () {
    config()->set('walletcard.google.issuer_id', '1234567890');
    config()->set('walletcard.google.service_account', fakeGoogleServiceAccount());

    $card = Card::factory()->create();

    $this->get(route('cards.google', $card->code))
        ->assertRedirectContains('pay.google.com');
});

it('renvoie 503 sur la route google quand non configuré', function () {
    config()->set('walletcard.google.issuer_id', '');

    $card = Card::factory()->create();

    $this->get(route('cards.google', $card->code))->assertStatus(503);
});
