@props([
    'bgColor' => '#DD7FF9',
    'textColor' => '#FFFFFF',
    'qrColor' => '#000000',
    'title' => '',
    'subtitle' => '',
    'fields' => [],
    'backFields' => [],
    'imageUrl' => null,
    'qrPayload' => null,
])

@php
    use App\Support\Color;
    $qrRgb = Color::hexToRgb($qrColor);
    $clean = fn ($items) => collect($items)
        ->map(fn ($f) => ['label' => trim((string)($f['label'] ?? '')), 'value' => trim((string)($f['value'] ?? ''))])
        ->filter(fn ($f) => $f['label'] !== '' || $f['value'] !== '');
    $frontFields = $clean($fields)->take(4);
    $backFieldsClean = $clean($backFields);
    $hasBack = $backFieldsClean->isNotEmpty();
@endphp

<div x-data="{ flipped: false }" {{ $attributes->merge(['class' => 'w-full max-w-[340px] mx-auto']) }} style="perspective: 1200px;">
    <div class="relative transition-transform duration-500" :class="{ 'is-flipped': flipped }" style="transform-style: preserve-3d;">

        {{-- RECTO --}}
        <div class="relative rounded-[28px] p-6 overflow-hidden select-none"
             style="background-color: {{ $bgColor }}; color: {{ $textColor }};
                    box-shadow: 0 20px 60px -15px rgba(0,0,0,.55); backface-visibility: hidden;">
            <div class="flex items-start justify-between mb-5">
                <div class="flex items-center gap-2 text-[13px] font-semibold tracking-tight opacity-90">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="4" y="3" width="13" height="18" rx="3" fill="currentColor" opacity="0.95"/>
                        <circle cx="17.5" cy="18.5" r="4.5" fill="{{ $bgColor }}"/>
                        <path d="M15.5 18.5h4M17.5 16.5v4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                    </svg>
                    WalletCard
                </div>
                <div class="flex items-center gap-2">
                    @if ($imageUrl)
                        <img src="{{ $imageUrl }}" alt="" class="w-12 h-12 rounded-xl object-cover ring-1 ring-white/25">
                    @endif
                    @if ($hasBack)
                        <button type="button" @click="flipped = true"
                                class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold"
                                style="background: {{ $textColor }}22; color: {{ $textColor }};" aria-label="Voir le dos">ⓘ</button>
                    @endif
                </div>
            </div>

            @if ($qrPayload)
                <div class="flex justify-center mb-5">
                    <div class="rounded-2xl bg-white p-2.5">
                        <div class="w-[164px] h-[164px] [&>svg]:w-full [&>svg]:h-full">
                            {!! qr_svg($qrPayload, 164, $qrRgb) !!}
                        </div>
                    </div>
                </div>
            @else
                <div class="flex justify-center mb-5">
                    <div class="w-[180px] h-[180px] rounded-2xl border-2 border-dashed flex items-center justify-center text-center text-xs px-4"
                         style="border-color: {{ $textColor }}33; color: {{ $textColor }}99;">
                        Pas de QR — juste une carte
                    </div>
                </div>
            @endif

            <div class="text-center">
                <div class="font-title font-bold text-2xl leading-tight break-words">{{ $title !== '' ? $title : 'Ma carte' }}</div>
                @if ($subtitle !== '')
                    <div class="text-sm mt-0.5 opacity-75">{{ $subtitle }}</div>
                @endif
            </div>

            @if ($frontFields->isNotEmpty())
                <div class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3">
                    @foreach ($frontFields as $field)
                        <div class="min-w-0">
                            @if ($field['label'] !== '')
                                <div class="text-[10px] uppercase tracking-wide opacity-60">{{ $field['label'] }}</div>
                            @endif
                            <div class="text-sm font-medium truncate">{{ $field['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            <svg class="absolute bottom-4 right-5 opacity-40" width="54" height="22" viewBox="0 0 54 22" fill="none" aria-hidden="true">
                <path d="M2 14c6-10 9 4 14 2s5-12 10-9-1 14 6 11 8-9 14-7" stroke="{{ $textColor }}" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
        </div>

        {{-- VERSO --}}
        @if ($hasBack)
            <div class="absolute inset-0 rounded-[28px] p-6 overflow-y-auto no-scrollbar"
                 style="background-color: {{ $bgColor }}; color: {{ $textColor }};
                        box-shadow: 0 20px 60px -15px rgba(0,0,0,.55);
                        backface-visibility: hidden; transform: rotateY(180deg);">
                <div class="flex items-center justify-between mb-4">
                    <span class="font-title text-lg font-bold">{{ $title !== '' ? $title : 'Ma carte' }}</span>
                    <button type="button" @click="flipped = false"
                            class="flex h-7 w-7 items-center justify-center rounded-full text-sm"
                            style="background: {{ $textColor }}22; color: {{ $textColor }};" aria-label="Voir le recto">↺</button>
                </div>
                <div class="space-y-3">
                    @foreach ($backFieldsClean as $field)
                        <div>
                            @if ($field['label'] !== '')
                                <div class="text-[10px] uppercase tracking-wide opacity-60">{{ $field['label'] }}</div>
                            @endif
                            <div class="text-sm leading-snug break-words" style="opacity:.95">{{ $field['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
