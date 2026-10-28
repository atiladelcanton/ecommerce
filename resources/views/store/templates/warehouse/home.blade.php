<div class="store-container wh-home">
    <div class="wh-home-hero-layout">
        <aside class="wh-home-sidebar" aria-label="Categorias da loja">
            <h2><x-heroicon-o-bars-3 class="size-5" aria-hidden="true" /> Compre por departamento</h2>
            <nav aria-label="Navegar por departamentos">
                @forelse($categories as $category)
                    <a href="{{ route('store.products', ['categoria' => $category->slug]) }}">{{ $category->name }} <x-heroicon-o-chevron-right class="size-4" aria-hidden="true" /></a>
                @empty
                    <span class="wh-side-empty">Categorias em breve</span>
                @endforelse
            </nav>
            <a class="wh-side-all" href="{{ route('store.products') }}">Todo o catálogo →</a>
        </aside>
        <div class="wh-home-feature">
            @if($banners->isNotEmpty())
                <h1 class="sr-only">{{ $storeName }} — produtos e novidades</h1>
                <x-store.hero-carousel :banners="$banners" />
            @else
                <section class="wh-home-intro">
                    <span class="wh-overline">Bem-vindo à {{ $storeName }}</span>
                    <h1>Seu próximo projeto começa aqui.</h1>
                    <p>Encontre equipamentos, insumos e acessórios em um catálogo organizado para facilitar sua compra.</p>
                    <a class="store-button" href="{{ route('store.products') }}">Explorar produtos <x-heroicon-o-arrow-right class="size-4" /></a>
                </section>
            @endif
            <div class="wh-home-benefits" aria-label="Navegue pela loja">
                <a href="{{ route('store.products') }}"><x-heroicon-o-squares-2x2 class="size-6" /><span><strong>Variedade de produtos</strong><small>Explore todo o catálogo</small></span></a>
                <a href="{{ route('store.products', ['promocao' => 1]) }}"><x-heroicon-o-tag class="size-6" /><span><strong>Ofertas especiais</strong><small>Acompanhe os descontos</small></span></a>
                <a href="{{ route('store.contact') }}"><x-heroicon-o-chat-bubble-oval-left-ellipsis class="size-6" /><span><strong>Precisa de ajuda?</strong><small>Fale com nosso atendimento</small></span></a>
            </div>
        </div>
    </div>
    <section id="categorias" class="wh-section wh-home-categories">
        <div class="wh-section-title"><div><span class="wh-overline">Encontre o que precisa</span><h2>Categorias em destaque</h2></div><a href="{{ route('store.products') }}">Todos os produtos →</a></div>
        <div class="wh-category-grid">
            @forelse($categories as $category)
                <a class="wh-category-tile" href="{{ route('store.products', ['categoria' => $category->slug]) }}">
                    @if($category->image_path)
                        <img src="{{ Storage::disk('public')->url($category->image_path) }}" alt="" loading="lazy" width="350" height="220">
                    @else
                        <span class="wh-category-placeholder"><x-heroicon-o-squares-2x2 class="size-9" aria-hidden="true" /></span>
                    @endif
                    <span>{{ $category->name }}</span>
                </a>
            @empty
                <p>As categorias estarão disponíveis em breve.</p>
            @endforelse
        </div>
    </section>
    @if($featured->isNotEmpty())
    <section class="wh-section"><div class="wh-section-title"><div><span class="wh-overline">Nossa seleção</span><h2>Produtos em destaque</h2></div><a href="{{ route('store.products') }}">Ver catálogo →</a></div><div class="store-product-grid">@foreach($featured as $product)<x-store.product-card :product="$product" />@endforeach</div></section>
    @endif
    @if($promotions->isNotEmpty())
    <section class="wh-section" data-section="promotions"><div class="wh-section-title"><div><span class="wh-overline">Não perca</span><h2>Ofertas especiais</h2></div><a href="{{ route('store.products', ['promocao' => 1]) }}">Ver promoções →</a></div><div class="store-product-grid">@foreach($promotions as $product)<x-store.product-card :product="$product" />@endforeach</div></section>
    @endif
    @if($latestBlogPosts->isNotEmpty())
    <section class="wh-section" data-section="latest-blog"><div class="wh-section-title"><div><span class="wh-overline">Dicas, novidades e conteúdos</span><h2>Últimas do Blog</h2></div><a href="{{ route('store.blog.index') }}">Ver artigos →</a></div><div class="saba-blog-grid">@foreach($latestBlogPosts as $post)<x-store.blog-card :post="$post" />@endforeach</div></section>
    @endif
</div>
