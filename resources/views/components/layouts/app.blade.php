<!DOCTYPE html>
<html lang="fr" class="bg-bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'WalletCard — Crée ta carte Wallet. Mets ce que tu veux dedans.' }}</title>
    <meta name="description" content="{{ $description ?? 'Crée ta propre carte Apple Wallet / Google Wallet en 30 secondes. Snap, Insta, ton site, ce que tu veux. Gratuit, sans compte.' }}">

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon.png') }}">

    <meta property="og:title" content="{{ $title ?? 'WalletCard' }}">
    <meta property="og:description" content="{{ $description ?? 'Crée ta carte Wallet. Mets ce que tu veux dedans.' }}">
    <meta property="og:type" content="website">

    @isset($head){{ $head }}@endisset

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-dvh bg-bg text-ink font-body antialiased">
    <div class="min-h-dvh bg-aurora">
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>
