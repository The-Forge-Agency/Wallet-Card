<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Support\Str;

class PwaController extends Controller
{
    /**
     * Manifest PWA dynamique par carte : permet "Ajouter à l'écran d'accueil"
     * sur Android/Samsung en pointant directement sur la carte.
     */
    public function manifest(Card $card)
    {
        $manifest = [
            'name' => $card->displayTitle().' — WalletCard',
            'short_name' => Str::limit($card->displayTitle(), 12, ''),
            'start_url' => route('cards.show', $card->code),
            'scope' => route('cards.show', $card->code),
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#0A0A0F',
            'theme_color' => $card->bg_color,
            'icons' => [
                ['src' => asset('images/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => asset('images/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
        ]);
    }

    public function serviceWorker()
    {
        $js = <<<'JS'
        const CACHE = 'walletcard-v1';
        self.addEventListener('install', (e) => self.skipWaiting());
        self.addEventListener('activate', (e) => e.waitUntil(self.clients.claim()));
        self.addEventListener('fetch', (e) => {
            if (e.request.method !== 'GET') return;
            e.respondWith(
                fetch(e.request)
                    .then((res) => {
                        const copy = res.clone();
                        caches.open(CACHE).then((c) => c.put(e.request, copy));
                        return res;
                    })
                    .catch(() => caches.match(e.request))
            );
        });
        JS;

        return response($js, 200, [
            'Content-Type' => 'application/javascript',
            'Service-Worker-Allowed' => '/',
        ]);
    }
}
