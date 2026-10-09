@php
    $pageTitle = $title ? $title.' | '.$storeName : ($settings?->seo_title ?: $storeName);
    $pageDescription = $description ?: ($settings?->seo_description ?: 'Conheça o catálogo de máquinas e equipamentos para serigrafia da '.$storeName.'.');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical ?: url()->current() }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical ?: url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:image" content="{{ $image ?: asset('images/saba-logo.jpeg') }}">
    <link rel="icon" href="{{ asset('images/saba-logo.jpeg') }}">
    <link rel="stylesheet" href="{{ asset('fonts/filament/filament/inter/index.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body {{ $attributes->class(['store-body']) }}>
    <a href="#conteudo" class="store-skip">Pular para o conteúdo</a>
    <x-store.header :categories="$navigationCategories" :whatsapp-url="$whatsappUrl" :store-name="$storeName" />
    <main id="conteudo">{{ $slot }}</main>
    <x-store.footer :settings="$settings" :store-name="$storeName" :whatsapp-url="$whatsappUrl" />
    @livewireScriptConfig
</body>
</html>
