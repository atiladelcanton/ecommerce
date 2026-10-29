@php
    $storefrontTemplate = \App\Support\Storefront\Template::active();
    $appearance = \App\Support\Storefront\Appearance::forTemplate($settings, $storefrontTemplate);
    $logoUrl = \App\Support\Storefront\Appearance::logoUrl($settings);
    $pageTitle = $title ? $title.' | '.$storeName : ($settings?->seo_title ?: $storeName);
    $pageDescription = $description ?: ($settings?->seo_description ?: 'Conheça o catálogo de produtos da '.$storeName.'.');
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
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:image" content="{{ $image ?: $logoUrl }}">
    <link rel="icon" href="{{ $logoUrl }}">
    <link rel="stylesheet" href="{{ asset('css/blog.css') }}">
    <link rel="stylesheet" href="{{ asset('fonts/filament/filament/inter/index.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if($storefrontTemplate === 2)
        <link rel="stylesheet" href="{{ asset('css/storefront/templates/warehouse.css') }}">
    @elseif(in_array($storefrontTemplate, [3, 4, 5], true))
        <link rel="stylesheet" href="{{ asset('css/storefront/templates/creative.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('css/storefront/appearance.css') }}">
    @livewireStyles
    @stack('head')
</head>
<body {{ $attributes->class(['store-body', 'store-template-2' => $storefrontTemplate === 2, 'store-template-3' => $storefrontTemplate === 3, 'store-template-4' => $storefrontTemplate === 4, 'store-template-5' => $storefrontTemplate === 5]) }} style="{{ $appearance['css'] }}">
    <a href="#conteudo" class="store-skip">Pular para o conteúdo</a>
    @if($storefrontTemplate === 2)
        <x-store.templates.warehouse.header :categories="$navigationCategories" :whatsapp-url="$whatsappUrl" :store-name="$storeName" :logo-url="$logoUrl" :blog-enabled="(bool) ($settings?->blog_enabled)" />
    @elseif(in_array($storefrontTemplate, [3, 4, 5], true))
        <x-store.templates.creative.header :template="$storefrontTemplate" :categories="$navigationCategories" :whatsapp-url="$whatsappUrl" :store-name="$storeName" :logo-url="$logoUrl" :blog-enabled="(bool) ($settings?->blog_enabled)" />
    @else
        <x-store.header :categories="$navigationCategories" :whatsapp-url="$whatsappUrl" :store-name="$storeName" :logo-url="$logoUrl" :blog-enabled="(bool) ($settings?->blog_enabled)" />
    @endif
    <main id="conteudo">{{ $slot }}</main>
    @if($storefrontTemplate === 2)
        <x-store.templates.warehouse.footer :settings="$settings" :store-name="$storeName" :whatsapp-url="$whatsappUrl" :logo-url="$logoUrl" />
    @elseif(in_array($storefrontTemplate, [3, 4, 5], true))
        <x-store.templates.creative.footer :template="$storefrontTemplate" :settings="$settings" :store-name="$storeName" :whatsapp-url="$whatsappUrl" :logo-url="$logoUrl" />
    @else
        <x-store.footer :settings="$settings" :store-name="$storeName" :whatsapp-url="$whatsappUrl" :logo-url="$logoUrl" />
    @endif
    <x-store.toast-container />
    @livewireScriptConfig
</body>
</html>
