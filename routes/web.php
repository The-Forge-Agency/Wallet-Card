<?php

use App\Http\Controllers\CardController;
use App\Http\Controllers\PwaController;
use App\Livewire\CardEditor;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::get('/create', CardEditor::class)->name('create');
Route::get('/e/{editToken}', CardEditor::class)->name('cards.edit');

Route::get('/c/{card}', [CardController::class, 'show'])->name('cards.show');
Route::get('/c/{card}/pass.pkpass', [CardController::class, 'pass'])->name('cards.pass');
Route::get('/c/{card}/google', [CardController::class, 'google'])->name('cards.google');
Route::get('/c/{card}/qr.svg', [CardController::class, 'qr'])->name('cards.qr');
Route::get('/c/{card}/manifest.webmanifest', [PwaController::class, 'manifest'])->name('cards.manifest');

Route::get('/sw.js', [PwaController::class, 'serviceWorker'])->name('pwa.sw');
