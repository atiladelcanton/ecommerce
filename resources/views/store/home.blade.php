<x-store.layout :canonical="route('home')">
    <div class="store-container pt-5">
        @if($banners->isNotEmpty())
            <h1 class="sr-only">Máquinas e equipamentos para serigrafia</h1>
            <x-store.hero-carousel :banners="$banners" />
        @else
            <section class="store-intro"><p class="store-eyebrow">SABA · Equipamentos serigráficos</p><h1 class="text-4xl sm:text-5xl lg:text-6xl font-semibold tracking-tight max-w-3xl leading-tight mt-6">O próximo passo<br>da sua produção.</h1><p class="mt-6 text-store-muted max-w-lg leading-7">Explore nosso catálogo de máquinas e equipamentos para serigrafia.</p><a class="store-button mt-8" href="{{ route('store.products') }}">Conhecer os produtos <x-heroicon-o-arrow-right class="size-4" /></a></section>
        @endif
        <section id="categorias" class="store-section scroll-mt-32">
            <div class="store-section-heading"><div><p class="store-eyebrow">Explore o catálogo</p><h2 class="store-title mt-2">Encontre por categoria</h2></div></div>
            <div class="flex flex-wrap gap-3">@forelse($categories as $category)<a class="store-category" href="{{ route('store.products', ['categoria' => $category->slug]) }}">{{ $category->name }} <x-heroicon-o-arrow-up-right class="size-4 text-store-muted" /></a>@empty<p class="text-store-muted">As categorias estarão disponíveis em breve.</p>@endforelse</div>
        </section>
        @if($featured->isNotEmpty())
        <section class="store-section"><div class="store-section-heading"><div><p class="store-eyebrow">Seleção SABA</p><h2 class="store-title mt-2">Produtos em destaque</h2></div><a class="store-text-link" href="{{ route('store.products') }}">Explorar catálogo →</a></div><div class="store-product-grid">@foreach($featured as $product)<x-store.product-card :product="$product" />@endforeach</div></section>
        @endif
        @if($promotions->isNotEmpty())
        <section class="store-section" data-section="promotions"><div class="store-section-heading"><div><p class="store-eyebrow text-store-red">Ofertas especiais</p><h2 class="store-title mt-2">Promoções</h2></div><a class="store-text-link" href="{{ route('store.products', ['promocao' => 1]) }}">Ver promoções →</a></div><div class="store-product-grid">@foreach($promotions as $product)<x-store.product-card :product="$product" />@endforeach</div></section>
        @endif
        <section class="store-section"><div class="store-section-heading"><div><p class="store-eyebrow">Nosso catálogo</p><h2 class="store-title mt-2">Conheça nossos equipamentos</h2></div><a class="store-text-link" href="{{ route('store.products') }}">Ver todos os produtos →</a></div>
            <div class="store-product-grid">@forelse($recent as $product)<x-store.product-card :product="$product" />@empty<p class="text-store-muted col-span-full py-10">Nenhum produto encontrado.</p>@endforelse</div>
        </section>
    </div>
</x-store.layout>
