@props(['template', 'categories', 'whatsappUrl', 'storeName', 'blogEnabled' => false, 'logoUrl' => null])
<div class="ct-announcement">
    <div class="store-container ct-announcement-inner">
        <span>{{ $template === 5 ? 'NOVIDADES, PRODUTOS E ATITUDE' : ($template === 4 ? 'Uma experiência de compra feita para você' : 'Bem-vindo à '.$storeName) }}</span>
        <a href="{{ route('store.contact') }}">Atendimento <span aria-hidden="true">↗</span></a>
    </div>
</div>
<header class="ct-header store-header" x-data="storeHeader" @keydown.escape="closeCategories()">
    <div class="store-container ct-header-main">
        @if($template === 4)
            <nav class="ct-header-utility" aria-label="Links rápidos">
                <a href="{{ route('store.products') }}">Loja</a>
                @if($blogEnabled)<a href="{{ route('store.blog.index') }}">Blog</a>@endif
            </nav>
        @endif
        <a class="ct-logo" href="{{ route('home') }}" aria-label="{{ $storeName }} — Início">
            <img src="{{ $logoUrl ?: asset('images/saba-logo.jpeg') }}" alt="{{ $storeName }}" width="185" height="65">
        </a>
        <div class="ct-header-search" id="ct-search" x-show="desktop || searchOpen" x-cloak>
            <livewire:store.search-autocomplete />
        </div>
        <div class="ct-header-actions">
            <a class="ct-account" href="{{ auth('customer')->check() ? route('customer.dashboard') : route('customer.login') }}" aria-label="Minha conta"><x-heroicon-o-user class="size-6" aria-hidden="true" /><span>Minha conta</span></a>
            <div class="ct-cart"><livewire:store.cart-badge /></div>
            <button class="ct-mobile-button ct-mobile-search" type="button" @click="toggleSearch()" :aria-expanded="searchOpen" aria-controls="ct-search" aria-label="Pesquisar produtos"><x-heroicon-o-magnifying-glass class="size-6" /></button>
            <button class="ct-mobile-button" type="button" @click="$refs.menu.showModal()" aria-controls="ct-menu" aria-haspopup="dialog" aria-label="Abrir menu"><x-heroicon-o-bars-3 class="size-6" /></button>
        </div>
    </div>
    <div class="ct-navigation">
        <nav class="store-container ct-navigation-inner" aria-label="Navegação principal">
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Início</a>
            <a href="{{ route('store.products') }}" @if(request()->routeIs('store.products')) aria-current="page" @endif>Produtos</a>
            <details class="ct-nav-categories">
                <summary>Categorias <x-heroicon-o-chevron-down class="size-4" aria-hidden="true" /></summary>
                <ul>
                    @forelse($categories as $category)
                        <li><a href="{{ route('store.products', ['categoria' => $category->slug]) }}">{{ $category->name }}</a></li>
                    @empty<li><span>Nenhuma categoria cadastrada</span></li>@endforelse
                </ul>
            </details>
            @if($blogEnabled)<a href="{{ route('store.blog.index') }}" @if(request()->routeIs('store.blog.*')) aria-current="page" @endif>Blog</a>@endif
            <a href="{{ route('store.about') }}">Quem Somos</a>
            <a href="{{ route('store.contact') }}">Contato</a>
            @if($whatsappUrl)<a class="ct-nav-contact" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>@endif
        </nav>
    </div>
    <dialog id="ct-menu" x-ref="menu" class="store-dialog ct-menu" aria-labelledby="ct-menu-title">
        <div class="store-drawer-heading"><h2 id="ct-menu-title">Navegue pela loja</h2><button type="button" class="store-icon-btn" @click="$refs.menu.close()" aria-label="Fechar menu"><x-heroicon-o-x-mark class="size-6" /></button></div>
        <nav class="store-drawer-nav" aria-label="Navegação mobile">
            <a href="{{ route('home') }}">Início</a><a href="{{ route('store.products') }}">Produtos</a>
            <details class="store-drawer-categories"><summary>Categorias <x-heroicon-o-chevron-down class="size-4" /></summary><ul>
                @foreach($categories as $category)<li><a href="{{ route('store.products', ['categoria' => $category->slug]) }}">{{ $category->name }}</a></li>@endforeach
            </ul></details>
            @if($blogEnabled)<a href="{{ route('store.blog.index') }}">Blog</a>@endif
            <a href="{{ route('store.about') }}">Quem Somos</a><a href="{{ route('store.contact') }}">Contato</a>
            <a href="{{ auth('customer')->check() ? route('customer.dashboard') : route('customer.login') }}">Minha conta</a>
            @if($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Falar pelo WhatsApp</a>@endif
        </nav>
    </dialog>
</header>
