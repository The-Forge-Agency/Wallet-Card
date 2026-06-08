@props(['size' => 'h-9'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <img src="{{ asset('images/icon-mark.png') }}" alt="WalletCard" class="{{ $size }} w-auto">
    <span class="font-title text-2xl font-bold tracking-tight leading-none">Wallet<span style="color: var(--color-accent)">Card</span></span>
</span>
