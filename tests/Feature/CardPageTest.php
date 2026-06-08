<?php

use App\Models\Card;

it('affiche la landing', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Crée ta carte');
});

it('affiche la page de partage d\'une carte', function () {
    $card = Card::factory()->create();

    $this->get(route('cards.show', $card->code))
        ->assertOk()
        ->assertSee('Copier le lien')
        ->assertSee($card->title);
});

it('télécharge le QR en SVG', function () {
    $card = Card::factory()->create();

    $this->get(route('cards.qr', $card->code))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml');
});

it('renvoie 404 pour le QR si la carte n\'en a pas', function () {
    $card = Card::factory()->withoutQr()->create();

    $this->get(route('cards.qr', $card->code))->assertNotFound();
});

it('renvoie un manifest PWA valide', function () {
    $card = Card::factory()->create();

    $this->get(route('cards.manifest', $card->code))
        ->assertOk()
        ->assertJsonPath('display', 'standalone')
        ->assertJsonPath('theme_color', $card->bg_color);
});

it('renvoie 503 sur le pass quand Apple n\'est pas configuré', function () {
    config()->set('walletcard.pass_type_identifier', '');

    $card = Card::factory()->create();

    $this->get(route('cards.pass', $card->code))->assertStatus(503);
});

it('sert le service worker', function () {
    $this->get(route('pwa.sw'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/javascript');
});
