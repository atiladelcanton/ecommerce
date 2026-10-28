@props(['categories', 'whatsappUrl', 'storeName', 'blogEnabled' => false])
<div class="wh-promo-bar">
    <div class="store-container wh-promo-inner">
        <span>Bem-vindo à {{ $storeName }}</span>
        <span>Atendimento e produtos selecionados para você</span>
        <a href="{{ route('store.contact') }}">Central de atendimento</a>
    </div>
</div>
<header class="wh-header store-header" x-data="storeHeader" @keydown.escape="closeCategories()">
    <div class="store-container wh-mainbar">
        <a href="{{ route('home') }}" class="wh-logo" aria-label="{{ $storeName }} — início">
            <img src="{{ asset('images/saba-logo.jpeg') }}" alt="{{ $storeName }}" width="180" height="66">
        </a>
        <div id="wh-header-search" class="wh-search" x-show="desktop || searchOpen" x-cloak>
            <livewire:store.search-autocomplete />
        </div>
        <div class="wh-mainbar-actions">
            <a class="wh-account-link" href="{{ auth('customer')->check() ? route('customer.dashboard') : route('customer.login') }}">
                <x-heroicon-o-user class="size-6" aria-hidden="true" />
                <span>{{ auth('customer')->check() ? 'Minha conta' : 'Entrar' }}</span>
            </a>
            <div class="wh-cart"><livewire:store.cart-badge /></div>
            <button type="button" class="wh-mobile-btn" @click="toggleSearch()" :aria-expanded="searchOpen" aria-controls="wh-header-search" aria-label="Buscar produtos"><x-heroicon-o-magnifying-glass class="size-6" /></button>
            <button type="button" class="wh-mobile-btn" @click="$refs.menu.showModal()" aria-controls="wh-mobile-menu" aria-haspopup="dialog" aria-label="Abrir menu"><x-heroicon-o-bars-3 class="size-6" /></button>
        </div>
    </div>
    <div class="wh-nav-bar">
        <nav class="store-container wh-nav-inner" aria-label="Navegação principal">
            <div class="wh-menu-wrapper" @mouseenter="openCategories()" @mouseleave="categoriesOpen = false" @focusout="if (!$el.contains($event.relatedTarget)) categoriesOpen = false" @click.outside="categoriesOpen = false">
                <button type="button" class="wh-categories-trigger" x-ref="categoriesTrigger" @click="categoriesOpen = !categoriesOpen" :aria-expanded="categoriesOpen" aria-controls="wh-category-menu">
                    <x-heroicon-o-bars-3 class="size-5" aria-hidden="true" /> Departamentos <x-heroicon-o-chevron-down class="size-4" aria-hidden="true" />
                </button>
                <div id="wh-category-menu" class="wh-category-menu" x-show="categoriesOpen" x-cloak>
                    <ul>
                        @forelse($categories as $category)
                            <li><a href="{{ route('store.products', ['categoria' => $category->slug]) }}">{{ $category->name }} <x-heroicon-o-chevron-right class="size-4" aria-hidden="true" /></a></li>
                        @empty
                            <li class="wh-menu-empty">Nenhuma categoria disponível.</li>
                        @endforelse
                    </ul>
                    <a href="{{ route('store.products') }}" class="wh-category-all">Ver todos os produtos →</a>
                </div>
            </div>
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Início</a>
            <a href="{{ route('store.products') }}" @if(request()->routeIs('store.products')) aria-current="page" @endif>Produtos</a>
            <a href="{{ route('store.products', ['promocao' => 1]) }}">Promoções</a>
            @if($blogEnabled)<a href="{{ route('store.blog.index') }}" @if(request()->routeIs('store.blog.*')) aria-current="page" @endif>Blog</a>@endif
            <a href="{{ route('store.about') }}" @if(request()->routeIs('store.about')) aria-current="page" @endif>Quem Somos</a>
            <a href="{{ route('store.contact') }}" @if(request()->routeIs('store.contact')) aria-current="page" @endif>Contato</a>
            @if($whatsappUrl)
                <a class="wh-nav-whatsapp" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"><x-heroicon-o-chat-bubble-oval-left-ellipsis class="size-5" /> Atendimento por WhatsApp</a>
            @endif
        </nav>
    </div>
    <dialog id="wh-mobile-menu" x-ref="menu" class="store-dialog wh-mobile-menu" aria-labelledby="wh-mobile-menu-title">
        <div class="store-drawer-heading"><h2 id="wh-mobile-menu-title">Navegação</h2><button type="button" class="store-icon-btn" @click="$refs.menu.close()" aria-label="Fechar menu"><x-heroicon-o-x-mark class="size-6" /></button></div>
        <nav class="store-drawer-nav" aria-label="Navegação mobile">
            <a href="{{ route('home') }}">Início</a>
            <a href="{{ route('store.products') }}">Produtos</a>
            <details class="store-drawer-categories">
                <summary>Departamentos <x-heroicon-o-chevron-down class="size-4" /></summary>
                <ul>@foreach($categories as $category)<li><a href="{{ route('store.products', ['categoria' => $category->slug]) }}">{{ $category->name }}</a></li>@endforeach</ul>
            </details>
            <a href="{{ route('store.products', ['promocao' => 1]) }}">Promoções</a>
            @if($blogEnabled)<a href="{{ route('store.blog.index') }}">Blog</a>@endif
            <a href="{{ route('store.about') }}">Quem Somos</a>
            <a href="{{ route('store.contact') }}">Contato</a>
            <a href="{{ auth('customer')->check() ? route('customer.dashboard') : route('customer.login') }}">Minha conta</a>
            @if($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>@endif
        </nav>
    </dialog>
</header>
