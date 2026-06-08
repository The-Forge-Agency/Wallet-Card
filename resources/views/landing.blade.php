<x-layouts.app>
    <header class="mx-auto flex w-full max-w-6xl items-center justify-between px-5 py-5">
        <img src="{{ asset('images/logo.svg') }}" alt="WalletCard" class="h-7">
        <a href="{{ route('create') }}" wire:navigate
           class="btn-accent hidden px-5 py-2.5 text-sm font-semibold sm:inline-block">Créer ma carte</a>
    </header>

    {{-- HERO --}}
    <section class="mx-auto grid w-full max-w-6xl items-center gap-12 px-5 pb-16 pt-8 lg:grid-cols-2 lg:gap-8 lg:pt-16">
        <div class="text-center lg:text-left">
            <p class="mb-4 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs text-ink-alt"
               style="border-color: var(--color-border);">
                <span class="h-1.5 w-1.5 rounded-full" style="background: var(--color-accent)"></span>
                100% gratuit · sans compte
            </p>
            <h1 class="font-title text-4xl font-bold leading-[1.05] sm:text-5xl lg:text-6xl">
                Crée ta carte<br>
                <span style="color: var(--color-accent)">Wallet.</span>
            </h1>
            <p class="mx-auto mt-5 max-w-md text-lg text-ink-alt lg:mx-0">
                Mets ton Snap, ton Insta, ton site — ce que tu veux. En 30 secondes c'est dans ton Apple Wallet.
                Pas besoin d'être une entreprise.
            </p>
            <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row lg:justify-start">
                <a href="{{ route('create') }}" wire:navigate
                   class="btn-accent w-full px-7 py-3.5 text-base font-semibold sm:w-auto">
                    Créer ma carte →
                </a>
                <span class="text-sm text-ink-alt">Gratuit. Sans app à installer.</span>
            </div>
        </div>

        {{-- Cartes animées --}}
        <div class="relative flex h-[420px] items-center justify-center">
            <div class="absolute animate-float-slow" style="transform: rotate(-8deg) translateX(-60px);">
                <x-card-visual class="!max-w-[260px] scale-90 opacity-90"
                    bg-color="#3B82F6" text-color="#FFFFFF"
                    title="@studio" subtitle="Portfolio"
                    qr-payload="https://walletcard.tfa52.app" />
            </div>
            <div class="relative z-10 animate-float">
                <x-card-visual class="!max-w-[280px]"
                    bg-color="#DD7FF9" text-color="#FFFFFF"
                    title="@alvine" subtitle="Snapchat"
                    qr-payload="https://www.snapchat.com/add/alvine" />
            </div>
        </div>
    </section>

    {{-- COMMENT ÇA MARCHE --}}
    <section class="mx-auto w-full max-w-5xl px-5 py-12">
        <h2 class="text-center font-title text-2xl font-bold sm:text-3xl">Trois étapes, c'est tout</h2>
        <div class="mt-10 grid gap-6 sm:grid-cols-3">
            @foreach ([
                ['1', 'Choisis ton contenu', 'Snap, Insta, LinkedIn, ton site ou juste du texte. Le QR pointe où tu veux.'],
                ['2', 'Personnalise', 'Couleur, image, titre. Aperçu en direct, modifie jusqu\'à ce que ça te plaise.'],
                ['3', 'Ajoute à ton Wallet', 'Un tap et la carte est dans Apple Wallet. Partage le lien à qui tu veux.'],
            ] as [$n, $t, $d])
                <div class="rounded-card border p-6" style="border-color: var(--color-border); background-color: var(--color-bg-alt);">
                    <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-full font-title font-bold"
                         style="background: var(--color-accent); color: #fff;">{{ $n }}</div>
                    <h3 class="font-title text-lg font-bold">{{ $t }}</h3>
                    <p class="mt-2 text-sm text-ink-alt">{{ $d }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('create') }}" wire:navigate
               class="btn-accent inline-block px-8 py-3.5 text-base font-semibold">Crée la tienne →</a>
        </div>
    </section>

    <footer class="mx-auto w-full max-w-6xl px-5 py-10 text-center text-sm text-ink-alt">
        <img src="{{ asset('images/icon.svg') }}" alt="" class="mx-auto mb-3 h-8 w-8">
        WalletCard — Crée ta carte, mets ce que tu veux, c'est à toi.
    </footer>
</x-layouts.app>
