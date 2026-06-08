@php
    $types = [
        'snapchat'  => 'Snapchat',
        'instagram' => 'Instagram',
        'linkedin'  => 'LinkedIn',
        'url'       => 'Site web',
        'text'      => 'Texte libre',
        'none'      => 'Aucun QR',
    ];
@endphp

<div class="mx-auto w-full max-w-5xl px-4 pb-16 pt-6 sm:pt-10">
    {{-- Barre de progression --}}
    <div class="mx-auto mb-8 flex max-w-md items-center justify-center gap-2">
        @foreach ([1, 2] as $i)
            <div class="h-1.5 flex-1 rounded-full transition-colors duration-300"
                 style="background-color: {{ $step >= $i ? 'var(--color-accent)' : 'var(--color-border)' }};"></div>
        @endforeach
    </div>

    {{-- ÉTAPE 1 — choix du contenu --}}
    @if ($step === 1)
        <div class="mx-auto max-w-xl text-center">
            <a href="{{ route('home') }}" wire:navigate class="mb-8 inline-flex items-center gap-2">
                <img src="{{ asset('images/logo.svg') }}" alt="WalletCard" class="h-7">
            </a>

            <h1 class="font-title text-3xl font-bold leading-tight sm:text-4xl">
                Que veux-tu mettre dans<br>ton <span style="color: var(--color-accent)">Wallet</span> ?
            </h1>
            <p class="mt-3 text-ink-alt">Choisis ce que tu veux partager. C'est ta carte, fais comme tu veux.</p>

            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($types as $value => $label)
                    <button type="button" wire:click="selectQrType('{{ $value }}')"
                            class="group flex flex-col items-center gap-2 rounded-2xl border p-5 transition-all duration-200"
                            style="border-color: {{ $qr_type === $value ? 'var(--color-accent)' : 'var(--color-border)' }};
                                   background-color: {{ $qr_type === $value ? 'rgba(221,127,249,.12)' : 'var(--color-bg-alt)' }};">
                        <x-qr-icon :type="$value" class="h-6 w-6"
                                   :style="'color: '.($qr_type === $value ? 'var(--color-accent)' : 'var(--color-ink)')" />
                        <span class="text-sm font-medium">{{ $label }}</span>
                    </button>
                @endforeach
            </div>

            @if ($qr_type !== 'none')
                <div class="mt-6 text-left" wire:key="qr-input">
                    <label class="mb-1.5 block text-sm font-medium text-ink-alt">
                        {{ in_array($qr_type, ['url', 'text']) ? $types[$qr_type] : 'Ton pseudo '.$types[$qr_type] }}
                    </label>
                    <input type="text" wire:model.live.debounce.400ms="qr_value"
                           placeholder="{{ \App\Enums\QrType::from($qr_type)->placeholder() }}"
                           class="field w-full px-4 py-3 text-base">
                </div>
            @endif

            <button type="button" wire:click="goToStep(2)"
                    class="btn-accent mt-8 w-full py-3.5 text-base font-semibold">
                Continuer →
            </button>

            <p class="mt-5 flex items-center justify-center gap-1.5 text-sm text-ink-alt">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12l1-8.5Z" fill="var(--color-accent)"/></svg>
                Gratuit. Sans compte. En 30 secondes.
            </p>
        </div>
    @endif

    {{-- ÉTAPE 2 — personnalisation + preview live --}}
    @if ($step === 2)
        <div class="mb-6 flex items-center justify-center gap-2 text-center">
            @unless ($card)
                <button type="button" wire:click="goToStep(1)" class="absolute left-4 text-ink-alt hover:text-ink sm:left-8" aria-label="Retour">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            @endunless
            <div>
                <h2 class="font-title text-xl font-bold">Personnalise ta carte</h2>
                <p class="text-sm text-ink-alt">Aperçu en direct. Modifie ce que tu veux.</p>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-2 lg:items-start">
            {{-- Aperçu --}}
            <div class="lg:sticky lg:top-8">
                <x-card-visual
                    :bg-color="$bg_color"
                    :text-color="$text_color"
                    :qr-color="$qr_color"
                    :title="$title"
                    :subtitle="$subtitle"
                    :fields="$fields"
                    :back-fields="$back_fields"
                    :image-url="$this->imagePreviewUrl"
                    :qr-payload="$this->qrPreview" />
                @if (count(array_filter($back_fields, fn ($f) => trim(($f['label'] ?? '').($f['value'] ?? '')) !== '')))
                    <p class="mt-3 text-center text-xs text-ink-alt">Touche ⓘ sur la carte pour voir le dos.</p>
                @endif
            </div>

            {{-- Formulaire --}}
            <div class="space-y-6">
                <div class="rounded-card border p-5" style="border-color: var(--color-border); background-color: var(--color-bg-alt);">
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink-alt">Nom affiché</label>
                            <input type="text" wire:model.live.debounce.300ms="title" maxlength="60"
                                   placeholder="@alvine" class="field w-full px-3.5 py-2.5">
                            @error('title') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink-alt">Sous-titre</label>
                            <input type="text" wire:model.live.debounce.300ms="subtitle" maxlength="60"
                                   placeholder="Snapchat" class="field w-full px-3.5 py-2.5">
                        </div>
                    </div>
                </div>

                {{-- Couleurs --}}
                <div class="rounded-card border p-5" style="border-color: var(--color-border); background-color: var(--color-bg-alt);">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-ink-alt">Couleur de la carte</span>
                        <label class="flex items-center gap-2 text-xs text-ink-alt">
                            Perso
                            <input type="color" wire:model.live="bg_color" class="h-7 w-9 cursor-pointer rounded border-0 bg-transparent">
                        </label>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        @foreach ($presetColors as $color)
                            <button type="button" wire:click="setColor('{{ $color }}')"
                                    class="h-9 w-9 rounded-full transition-transform hover:scale-110"
                                    style="background-color: {{ $color }};
                                           outline: {{ $bg_color === $color ? '2px solid var(--color-ink)' : 'none' }};
                                           outline-offset: 2px;"
                                    aria-label="Couleur {{ $color }}"></button>
                        @endforeach
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm font-medium text-ink-alt">Couleur du texte</span>
                        <div class="flex gap-2">
                            <button type="button" wire:click="$set('text_color', '#FFFFFF')"
                                    class="h-7 w-7 rounded-full bg-white" style="outline: {{ $text_color === '#FFFFFF' ? '2px solid var(--color-accent)' : 'none' }}; outline-offset: 2px;"></button>
                            <button type="button" wire:click="$set('text_color', '#0A0A0F')"
                                    class="h-7 w-7 rounded-full" style="background:#0A0A0F; outline: {{ $text_color === '#0A0A0F' ? '2px solid var(--color-accent)' : 'none' }}; outline-offset: 2px;"></button>
                        </div>
                    </div>

                    @if ($qr_type !== 'none')
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-sm font-medium text-ink-alt">Couleur du QR</span>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="$set('qr_color', '#000000')"
                                        class="h-7 w-7 rounded-full" style="background:#000; outline: {{ $qr_color === '#000000' ? '2px solid var(--color-accent)' : 'none' }}; outline-offset: 2px;" aria-label="QR noir"></button>
                                <button type="button" wire:click="$set('qr_color', '#FFFFFF')"
                                        class="h-7 w-7 rounded-full border border-white/20 bg-white" style="outline: {{ $qr_color === '#FFFFFF' ? '2px solid var(--color-accent)' : 'none' }}; outline-offset: 2px;" aria-label="QR blanc"></button>
                                <input type="color" wire:model.live="qr_color" class="h-7 w-9 cursor-pointer rounded border-0 bg-transparent" aria-label="QR couleur perso">
                            </div>
                        </div>
                        <p class="mt-1 text-right text-[11px] text-ink-alt">Le QR est sur fond blanc — le noir scanne le mieux.</p>
                    @endif
                </div>

                {{-- Image --}}
                <div class="rounded-card border p-5" style="border-color: var(--color-border); background-color: var(--color-bg-alt);">
                    <label class="mb-2 block text-sm font-medium text-ink-alt">Image / logo (optionnel)</label>
                    <div class="flex items-center gap-4">
                        @if ($this->imagePreviewUrl)
                            <img src="{{ $this->imagePreviewUrl }}" alt="" class="h-14 w-14 rounded-xl object-cover">
                            <button type="button" wire:click="removeImage" class="text-sm text-error hover:underline">Retirer</button>
                        @endif
                        <label class="cursor-pointer rounded-btn border px-4 py-2 text-sm transition-colors hover:border-accent"
                               style="border-color: var(--color-border);">
                            <span wire:loading.remove wire:target="image">{{ $this->imagePreviewUrl ? 'Changer' : 'Choisir une image' }}</span>
                            <span wire:loading wire:target="image">Chargement…</span>
                            <input type="file" wire:model="image" accept="image/*" class="hidden">
                        </label>
                    </div>
                    @error('image') <p class="mt-2 text-xs text-error">{{ $message }}</p> @enderror
                </div>

                {{-- Champs face --}}
                <div class="rounded-card border p-5" style="border-color: var(--color-border); background-color: var(--color-bg-alt);">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-ink-alt">Champs sur la carte (max 4)</span>
                        @if (count($fields) < 4)
                            <button type="button" wire:click="addField" class="text-sm font-medium" style="color: var(--color-accent)">+ Ajouter</button>
                        @endif
                    </div>
                    <div class="space-y-3">
                        @forelse ($fields as $i => $field)
                            <div class="flex gap-2" wire:key="field-{{ $i }}">
                                <input type="text" wire:model.live.debounce.400ms="fields.{{ $i }}.label" placeholder="Label" class="field w-1/3 px-3 py-2 text-sm">
                                <input type="text" wire:model.live.debounce.400ms="fields.{{ $i }}.value" placeholder="Valeur" class="field flex-1 px-3 py-2 text-sm">
                                <button type="button" wire:click="removeField({{ $i }})" class="px-1 text-ink-alt hover:text-error" aria-label="Supprimer">✕</button>
                            </div>
                        @empty
                            <p class="text-sm text-ink-alt">Aucun champ. Ajoute une info si tu veux (email, ville, métier…).</p>
                        @endforelse
                    </div>
                </div>

                {{-- Champs au dos (Alpine : l'ouverture survit au re-render Livewire) --}}
                <div x-data="{ open: @js(count($back_fields) > 0) }"
                     class="rounded-card border p-5" style="border-color: var(--color-border); background-color: var(--color-bg-alt);">
                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between text-sm font-medium text-ink-alt">
                        Infos au dos de la carte (optionnel)
                        <svg class="transition-transform" :class="open && 'rotate-180'" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div x-show="open" x-collapse class="mt-3 space-y-3">
                        @foreach ($back_fields as $i => $field)
                            <div class="flex gap-2" wire:key="back-{{ $i }}">
                                <input type="text" wire:model.live.debounce.400ms="back_fields.{{ $i }}.label" placeholder="Titre" class="field w-1/3 px-3 py-2 text-sm">
                                <input type="text" wire:model.live.debounce.400ms="back_fields.{{ $i }}.value" placeholder="Contenu" class="field flex-1 px-3 py-2 text-sm">
                                <button type="button" wire:click="removeBackField({{ $i }})" class="px-1 text-ink-alt hover:text-error" aria-label="Supprimer">✕</button>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addBackField" class="text-sm font-medium" style="color: var(--color-accent)">+ Ajouter une info</button>
                    </div>
                </div>

                <button type="button" wire:click="save" wire:loading.attr="disabled"
                        class="btn-accent w-full py-3.5 text-base font-semibold">
                    <span wire:loading.remove wire:target="save">{{ $card ? 'Enregistrer les modifications' : 'Créer ma carte 🎉' }}</span>
                    <span wire:loading wire:target="save">Création…</span>
                </button>
            </div>
        </div>
    @endif
</div>
