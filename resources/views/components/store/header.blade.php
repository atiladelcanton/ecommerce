@props(['categories', 'whatsappUrl', 'storeName'])
<div class="store-announcement">
    <div class="store-container flex items-center justify-between gap-4 py-2">
        <span>Máquinas e equipamentos para serigrafia</span>
        @if($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="hidden sm:inline">Fale conosco pelo WhatsApp ↗</a>@endif
    </div>
</div>
<header class="store-header" x-data>
    <div class="store-container flex flex-wrap items-center gap-5 py-5 lg:flex-nowrap">
        <a href="{{ route('home') }}" aria-label="{{ $storeName }} — Início" class="shrink-0"><img src="{{ asset('images/saba-logo.jpeg') }}" alt="{{ $storeName }}" width="160" height="47" class="w-32 sm:w-40"></a>
        <nav aria-label="Navegação principal" class="hidden items-center gap-7 text-sm font-medium lg:flex lg:ml-10">
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Início</a>
            <a href="{{ route('store.products') }}" @if(request()->routeIs('store.products')) aria-current="page" @endif>Produtos</a>
            <details class="relative">
                <summary class="cursor-pointer">Categorias</summary>
                <div class="store-dropdown w-64">
                    @forelse($categories as $category)
                        <a class="block rounded-md px-3 py-2 hover:bg-store-soft" href="{{ route('store.products', ['categoria' => $category->slug]) }}">{{ $category->name }}</a>
                    @empty <span class="block p-3 text-store-muted">Nenhuma categoria disponível.</span> @endforelse
                </div>
            </details>
        </nav>
        <div class="order-last w-full lg:order-none lg:ml-auto lg:max-w-sm"><livewire:store.search-autocomplete /></div>
        @if($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="store-icon-btn ml-auto lg:ml-0" aria-label="Falar pelo WhatsApp"><x-heroicon-o-chat-bubble-oval-left-ellipsis class="size-6" /></a>@endif
        <button type="button" class="store-icon-btn lg:hidden {{ $whatsappUrl ? '' : 'ml-auto' }}" @click="$refs.menu.showModal()" aria-label="Abrir menu"><x-heroicon-o-bars-3 class="size-6" /></button>
    </div>
    <dialog x-ref="menu" class="store-dialog" aria-labelledby="mobile-menu-title">
        <div class="flex items-center justify-between mb-8"><h2 id="mobile-menu-title" class="text-xl font-semibold">Menu</h2><button class="store-icon-btn" @click="$refs.menu.close()" aria-label="Fechar menu"><x-heroicon-o-x-mark class="size-6" /></button></div>
        <nav class="flex flex-col gap-5" aria-label="Navegação mobile">
            <a href="{{ route('home') }}">Início</a><a href="{{ route('store.products') }}">Todos os produtos</a>
            <p class="store-eyebrow mt-4">Categorias</p>
            @foreach($categories as $category)<a href="{{ route('store.products', ['categoria' => $category->slug]) }}">{{ $category->name }}</a>@endforeach
        </nav>
    </dialog>
</header>
