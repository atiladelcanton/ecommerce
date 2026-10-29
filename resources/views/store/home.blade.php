<x-store.layout :canonical="route('home')" class="store-home">
    @if(\App\Support\Storefront\Template::is(2))
        @include('store.templates.warehouse.home')
    @elseif(\App\Support\Storefront\Template::is(3))
        @include('store.templates.horizonte.home')
    @elseif(\App\Support\Storefront\Template::is(4))
        @include('store.templates.essencia.home')
    @elseif(\App\Support\Storefront\Template::is(5))
        @include('store.templates.pulso.home')
    @else
    <div class="store-container store-home-content">
        @if($banners->isNotEmpty())
            <h1 class="sr-only">Catálogo de produtos de {{ $storeName }}</h1>
            <x-store.hero-carousel :banners="$banners" />
        @else
            <section class="store-intro"><p class="store-eyebrow">{{ $storeName }}</p><h1 class="text-4xl sm:text-5xl lg:text-6xl font-semibold tracking-tight max-w-3xl leading-tight mt-6">Encontre o produto<br>certo para você.</h1><p class="mt-6 text-store-muted max-w-lg leading-7">Explore o catálogo, compare opções e escolha a combinação ideal de cada produto.</p><a class="store-button mt-8" href="{{ route('store.products') }}">Conhecer os produtos <x-heroicon-o-arrow-right class="size-4" /></a></section>
        @endif
        <section id="categorias" class="store-section scroll-mt-32">
            <div class="store-section-heading"><div><p class="store-eyebrow">Explore o catálogo</p><h2 class="store-title mt-2">Encontre por categoria</h2></div></div>
            <div class="store-category-grid">
                @forelse($categories as $category)
                    <a class="store-category {{ $category->image_path ? 'store-category--image' : '' }}" href="{{ route('store.products', ['categoria' => $category->slug]) }}">
                        @if($category->image_path)
                            <img src="{{ Storage::disk('public')->url($category->image_path) }}" alt="" loading="lazy" width="640" height="440">
                            <span class="store-category-content">
                                <span class="store-category-name">{{ $category->name }}</span>
                                <x-heroicon-o-arrow-right class="size-4 shrink-0" aria-hidden="true" />
                            </span>
                        @else
                            <span>{{ $category->name }}</span> <x-heroicon-o-arrow-right class="size-4 shrink-0" aria-hidden="true" />
                        @endif
                    </a>
                @empty
                    <p class="text-store-muted col-span-full">As categorias estarão disponíveis em breve.</p>
                @endforelse
            </div>
        </section>
        @if($featured->isNotEmpty())
        <section class="store-section"><div class="store-section-heading"><div><p class="store-eyebrow">Seleção</p><h2 class="store-title mt-2">Produtos em destaque</h2></div><a class="store-text-link" href="{{ route('store.products') }}">Explorar catálogo →</a></div><div class="store-product-grid">@foreach($featured as $product)<x-store.product-card :product="$product" />@endforeach</div></section>
        @endif
        @if($promotions->isNotEmpty())
        <section class="store-section" data-section="promotions"><div class="store-section-heading"><div><p class="store-eyebrow text-store-red">Ofertas especiais</p><h2 class="store-title mt-2">Promoções</h2></div><a class="store-text-link" href="{{ route('store.products', ['promocao' => 1]) }}">Ver promoções →</a></div><div class="store-product-grid">@foreach($promotions as $product)<x-store.product-card :product="$product" />@endforeach</div></section>
        @endif
        @if($latestBlogPosts->isNotEmpty())
        <section class="store-section" data-section="latest-blog">
            <div class="store-section-heading">
                <div><p class="store-eyebrow">Conteúdo e novidades</p><h2 class="store-title mt-2">Últimas do Blog</h2></div>
                <a class="store-text-link" href="{{ route('store.blog.index') }}">Ver todas as postagens →</a>
            </div>
            <div class="saba-blog-grid">
                @foreach($latestBlogPosts as $post)
                    <x-store.blog-card :post="$post" />
                @endforeach
            </div>
        </section>
        @endif
    </div>
    @endif
</x-store.layout>
