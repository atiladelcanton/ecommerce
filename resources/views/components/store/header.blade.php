@props(['categories', 'whatsappUrl', 'storeName'])
<div class="store-announcement">
    <div class="store-container flex items-center justify-between gap-4 py-2">
        <span>Equipamentos, insumos e acessórios para serigrafia</span>
        @if($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="hidden sm:inline">Fale conosco pelo WhatsApp ↗</a>@endif
    </div>
</div>
<header class="store-header" x-data="storeHeader" @keydown.escape="closeCategories()">
    <div class="store-container store-header-main">
        <a href="{{ route('home') }}" aria-label="{{ $storeName }} — Início" class="store-header-logo">
            <img src="{{ asset('images/saba-logo.jpeg') }}" alt="{{ $storeName }}" width="192" height="56">
        </a>
        <div id="header-search" class="store-header-search" x-show="desktop || searchOpen" x-cloak>
            <livewire:store.search-autocomplete />
        </div>
        <a class="store-header-contact" href="{{ $whatsappUrl ?: route('store.contact') }}" @if($whatsappUrl) target="_blank" rel="noopener noreferrer" @endif>
            <x-heroicon-o-chat-bubble-oval-left-ellipsis class="size-6 shrink-0" />
            <span><span class="store-header-contact-caption">Fale com a SABA</span><span>{{ $whatsappUrl ? 'WhatsApp' : 'Contato' }}</span></span>
        </a>
        <a class="store-header-account" href="{{ auth('customer')->check() ? route('customer.dashboard') : route('customer.login') }}" aria-label="{{ auth('customer')->check() ? 'Minha conta' : 'Entrar na minha conta' }}">
            <x-heroicon-o-user class="size-6" aria-hidden="true" />
            <span>{{ auth('customer')->check() ? 'Minha conta' : 'Entrar' }}</span>
        </a>
        <div class="store-header-cart"><livewire:store.cart-badge /></div>
        <div class="store-header-mobile-controls">
            <button type="button" class="store-icon-btn" @click="toggleSearch()" :aria-expanded="searchOpen" aria-controls="header-search" aria-label="Abrir busca"><x-heroicon-o-magnifying-glass class="size-6" /></button>
            <button type="button" class="store-icon-btn" @click="$refs.menu.showModal()" aria-haspopup="dialog" aria-controls="mobile-menu" aria-label="Abrir menu"><x-heroicon-o-bars-3 class="size-6" /></button>
        </div>
    </div>
    <div class="store-header-navigation">
        <nav class="store-container store-header-nav" aria-label="Navegação principal">
            <a class="store-nav-link" href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Início</a>
            <a class="store-nav-link" href="{{ route('store.products') }}" @if(request()->routeIs('store.products')) aria-current="page" @endif>Produtos</a>
            <div class="store-mega-wrapper" @mouseenter="openCategories()" @mouseleave="categoriesOpen = false" @focusout="if (!$el.contains($event.relatedTarget)) categoriesOpen = false" @click.outside="categoriesOpen = false">
                <button type="button" class="store-nav-link store-mega-trigger" x-ref="categoriesTrigger" @click="categoriesOpen = !categoriesOpen" :aria-expanded="categoriesOpen" aria-controls="category-mega-menu">
                    Categorias <x-heroicon-o-chevron-down class="size-4" />
                </button>
                <div id="category-mega-menu" class="store-mega-menu" x-show="categoriesOpen" x-cloak>
                    <div class="store-mega-categories">
                        <p class="store-mega-label">Categorias</p>
                        <ul class="store-mega-category-grid">
                            @forelse($categories as $category)
                                <li><a href="{{ route('store.products', ['categoria' => $category->slug]) }}">{{ $category->name }}</a></li>
                            @empty
                                <li class="store-mega-empty">Nenhuma categoria disponível.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="store-mega-shortcuts">
                        <p class="store-mega-label">Atalhos</p>
                        <a href="{{ route('store.products') }}">Todos os produtos <x-heroicon-o-arrow-right class="size-4" /></a>
                        <a href="{{ route('store.products', ['promocao' => 1]) }}">Promoções <x-heroicon-o-arrow-right class="size-4" /></a>
                    </div>
                </div>
            </div>
            <a class="store-nav-link" href="{{ route('store.about') }}" @if(request()->routeIs('store.about')) aria-current="page" @endif>Quem Somos</a>
            <a class="store-nav-link" href="{{ route('store.contact') }}" @if(request()->routeIs('store.contact')) aria-current="page" @endif>Contato</a>
        </nav>
    </div>
    <dialog id="mobile-menu" x-ref="menu" class="store-dialog store-header-drawer" aria-labelledby="mobile-menu-title">
        <div class="store-drawer-heading">
            <h2 id="mobile-menu-title">Menu</h2>
            <button type="button" class="store-icon-btn" @click="$refs.menu.close()" aria-label="Fechar menu"><x-heroicon-o-x-mark class="size-6" /></button>
        </div>
        <nav class="store-drawer-nav" aria-label="Navegação mobile">
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Início</a>
            <a href="{{ route('store.products') }}" @if(request()->routeIs('store.products')) aria-current="page" @endif>Produtos</a>
            <details class="store-drawer-categories">
                <summary>Categorias <x-heroicon-o-chevron-down class="size-4" /></summary>
                <ul>
                    @forelse($categories as $category)
                        <li><a href="{{ route('store.products', ['categoria' => $category->slug]) }}">{{ $category->name }}</a></li>
                    @empty
                        <li class="store-mega-empty">Nenhuma categoria disponível.</li>
                    @endforelse
                </ul>
                <a href="{{ route('store.products', ['promocao' => 1]) }}">Promoções</a>
            </details>
            <a href="{{ route('store.about') }}" @if(request()->routeIs('store.about')) aria-current="page" @endif>Quem Somos</a>
            <a href="{{ route('store.contact') }}" @if(request()->routeIs('store.contact')) aria-current="page" @endif>Contato</a>
            <a href="{{ auth('customer')->check() ? route('customer.dashboard') : route('customer.login') }}">
                <x-heroicon-o-user class="size-5" /> {{ auth('customer')->check() ? 'Minha conta' : 'Entrar' }}
            </a>
            @if($whatsappUrl)
                <a class="store-drawer-whatsapp" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"><x-heroicon-o-chat-bubble-oval-left-ellipsis class="size-5" /> WhatsApp</a>
            @endif
        </nav>
    </dialog>
</header>
