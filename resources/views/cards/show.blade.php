<x-layouts.app :title="$card->displayTitle().' — WalletCard'">
    <x-slot:head>
        <meta name="theme-color" content="{{ $card->bg_color }}">
        <link rel="manifest" href="{{ route('cards.manifest', $card->code) }}">
    </x-slot:head>

    <div class="mx-auto w-full max-w-md px-5 py-8"
         x-data="{
            shareUrl: @js(route('cards.show', $card->code)),
            installEvent: null,
            init() {
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('{{ route('pwa.sw') }}').catch(() => {});
                }
                window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); this.installEvent = e; });
            },
            install() {
                if (this.installEvent) { this.installEvent.prompt(); this.installEvent = null; }
            },
            share() {
                if (navigator.share) { navigator.share({ title: 'Ma carte WalletCard', url: this.shareUrl }); }
            }
         }">

        @if (session('edit_token') === $card->edit_token)
            <div class="mb-6 rounded-card border p-4 text-center text-sm" style="border-color: var(--color-accent); background: rgba(221,127,249,.1);">
                <p class="font-medium">Ta carte est prête 🎉</p>
                <p class="mt-1 text-ink-alt">Garde ce lien pour la modifier plus tard :</p>
                <a href="{{ route('cards.edit', $card->edit_token) }}" wire:navigate
                   class="mt-1 inline-block break-all font-medium" style="color: var(--color-accent)">
                    {{ route('cards.edit', $card->edit_token) }}
                </a>
            </div>
        @endif

        <div class="mb-8">
            <x-card-visual
                :bg-color="$card->bg_color"
                :text-color="$card->text_color"
                :qr-color="$card->qr_color"
                :title="$card->title"
                :subtitle="$card->subtitle"
                :fields="$card->frontFields()"
                :back-fields="$card->backFields()"
                :image-url="$card->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($card->image_path) : null"
                :qr-payload="$card->qrPayload()" />
        </div>

        {{-- CTA principal selon l'appareil --}}
        <div class="space-y-3">
            @if ($device === 'ios')
                @if ($appleReady)
                    <a href="{{ route('cards.pass', $card->code) }}"
                       class="flex w-full items-center justify-center gap-2 rounded-btn bg-white py-3.5 text-base font-semibold text-black transition-transform active:scale-[.98]">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><rect x="3" y="6" width="18" height="13" rx="3" fill="black"/><rect x="3" y="9" width="18" height="2.5" fill="white"/></svg>
                        Ajouter à Apple Wallet
                    </a>
                @else
                    <div class="rounded-btn border px-4 py-3 text-center text-sm text-ink-alt" style="border-color: var(--color-border);">
                        L'ajout à Apple Wallet sera activé très bientôt. En attendant, partage ton lien 👇
                    </div>
                @endif
            @else
                @if ($googleReady)
                    <a href="{{ route('cards.google', $card->code) }}"
                       class="flex w-full items-center justify-center gap-2 rounded-btn bg-white py-3.5 text-base font-semibold text-black transition-transform active:scale-[.98]">
                        <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.5 12.2c0-.7-.1-1.4-.2-2H12v3.9h5.9a5 5 0 0 1-2.2 3.3v2.7h3.6c2.1-2 3.2-4.9 3.2-7.9Z"/><path fill="#34A853" d="M12 23c2.9 0 5.4-1 7.2-2.6l-3.6-2.7c-1 .7-2.3 1.1-3.6 1.1-2.8 0-5.2-1.9-6-4.4H2.3v2.8A11 11 0 0 0 12 23Z"/><path fill="#FBBC05" d="M6 14.4a6.6 6.6 0 0 1 0-4.2V7.4H2.3a11 11 0 0 0 0 9.8L6 14.4Z"/><path fill="#EA4335" d="M12 5.5c1.6 0 3 .5 4.1 1.6l3.1-3.1A11 11 0 0 0 2.3 7.4L6 10.2c.8-2.5 3.2-4.7 6-4.7Z"/></svg>
                        Ajouter à Google Wallet
                    </a>
                @endif

                <button type="button" @click="install()" x-show="installEvent"
                        class="{{ $googleReady ? 'border text-ink' : 'btn-accent' }} flex w-full items-center justify-center gap-2 py-3.5 text-base font-semibold"
                        @style(['border-color: var(--color-border)' => $googleReady])>
                    Ajouter à l'écran d'accueil
                </button>

                @unless ($googleReady)
                    <div x-show="!installEvent && {{ $device === 'desktop' ? 'true' : 'false' }}"
                         class="rounded-btn border px-4 py-3 text-center text-sm text-ink-alt" style="border-color: var(--color-border);">
                        Scanne le QR ci-dessus avec ton téléphone pour l'ajouter à ton Wallet.
                    </div>
                @endunless
            @endif
        </div>

        {{-- Partage --}}
        <p class="mt-8 mb-3 text-center text-sm text-ink-alt">Ou partage autrement</p>
        <div class="grid grid-cols-3 gap-3">
            <button type="button" x-data="copyButton(shareUrl)" @click="copy()"
                    class="flex flex-col items-center gap-1.5 rounded-card border py-4 text-xs transition-colors hover:border-accent" style="border-color: var(--color-border);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M9 5h8a2 2 0 0 1 2 2v10M7 9h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span x-text="copied ? 'Copié !' : 'Copier le lien'"></span>
            </button>

            @if ($card->hasQr())
                <a href="{{ route('cards.qr', $card->code) }}"
                   class="flex flex-col items-center gap-1.5 rounded-card border py-4 text-xs transition-colors hover:border-accent" style="border-color: var(--color-border);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 3v12m0 0-4-4m4 4 4-4M5 17v2a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Télécharger QR
                </a>
            @else
                <div class="flex flex-col items-center justify-center gap-1.5 rounded-card border py-4 text-xs opacity-40" style="border-color: var(--color-border);">
                    <span>Pas de QR</span>
                </div>
            @endif

            <button type="button" @click="share()"
                    class="flex flex-col items-center gap-1.5 rounded-card border py-4 text-xs transition-colors hover:border-accent" style="border-color: var(--color-border);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 16V4m0 0L8 8m4-4 4 4M6 12v6a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Partager
            </button>
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('home') }}" wire:navigate class="text-sm text-ink-alt hover:text-ink">← Créer une autre carte</a>
        </div>
    </div>
</x-layouts.app>
