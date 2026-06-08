<?php

use App\Livewire\CardEditor;
use App\Models\Card;
use Livewire\Livewire;

it('affiche le wizard de création', function () {
    Livewire::test(CardEditor::class)
        ->assertSet('step', 1)
        ->assertSee('snapchat', false);
});

it('crée une carte et redirige vers la page de partage', function () {
    Livewire::test(CardEditor::class)
        ->call('selectQrType', 'snapchat')
        ->set('qr_value', 'alvine')
        ->call('goToStep', 2)
        ->set('title', '@alvine')
        ->set('subtitle', 'Snapchat')
        ->set('bg_color', '#DD7FF9')
        ->call('save')
        ->assertRedirect();

    $card = Card::first();
    expect($card)->not->toBeNull()
        ->and($card->title)->toBe('@alvine')
        ->and($card->qr_type->value)->toBe('snapchat')
        ->and($card->qrPayload())->toBe('https://www.snapchat.com/add/alvine')
        ->and($card->code)->not->toBeEmpty()
        ->and($card->edit_token)->not->toBeEmpty();
});

it('refuse une couleur invalide', function () {
    Livewire::test(CardEditor::class)
        ->set('bg_color', 'pas-une-couleur')
        ->call('save')
        ->assertHasErrors('bg_color');
});

it('limite les champs face à 4', function () {
    $component = Livewire::test(CardEditor::class);

    foreach (range(1, 6) as $i) {
        $component->call('addField');
    }

    expect($component->get('fields'))->toHaveCount(4);
});

it('charge une carte existante via son edit token', function () {
    $card = Card::factory()->create(['title' => '@deja-la']);

    Livewire::test(CardEditor::class, ['editToken' => $card->edit_token])
        ->assertSet('step', 2)
        ->assertSet('title', '@deja-la');
});
