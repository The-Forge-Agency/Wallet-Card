<?php

namespace App\Http\Controllers;

use App\Exceptions\WalletPassUnavailableException;
use App\Models\Card;
use App\Services\AppleWalletService;
use App\Services\GoogleWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CardController extends Controller
{
    public function show(Card $card, AppleWalletService $apple, GoogleWalletService $google, Request $request)
    {
        return view('cards.show', [
            'card' => $card,
            'appleReady' => $apple->isConfigured(),
            'googleReady' => $google->isConfigured(),
            'device' => $this->detectDevice($request),
        ]);
    }

    public function google(Card $card, GoogleWalletService $google)
    {
        try {
            return redirect()->away($google->saveUrl($card));
        } catch (WalletPassUnavailableException) {
            abort(503, 'La génération Google Wallet n\'est pas encore activée.');
        }
    }

    public function pass(Card $card, AppleWalletService $wallet)
    {
        try {
            $content = $wallet->generate($card);
        } catch (WalletPassUnavailableException) {
            abort(503, 'La génération Apple Wallet n\'est pas encore activée.');
        }

        return response($content, 200, [
            'Content-Type' => 'application/vnd.apple.pkpass',
            'Content-Disposition' => 'attachment; filename="walletcard-'.$card->code.'.pkpass"',
        ]);
    }

    public function qr(Card $card)
    {
        abort_unless($card->hasQr(), 404);

        $svg = qr_svg($card->qrPayload(), 512, [0, 0, 0]);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="qr-'.$card->code.'.svg"',
        ]);
    }

    private function detectDevice(Request $request): string
    {
        $agent = Str::lower((string) $request->userAgent());

        if (Str::contains($agent, ['iphone', 'ipad', 'ipod'])) {
            return 'ios';
        }

        if (Str::contains($agent, 'android')) {
            return 'android';
        }

        return 'desktop';
    }
}
