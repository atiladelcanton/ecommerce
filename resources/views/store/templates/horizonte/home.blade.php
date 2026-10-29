<div class="ct-home ct-horizonte-home">
    <div class="ct-wide-hero">
        @if($banners->isNotEmpty())
            <h1 class="sr-only">{{ $storeName }} — catálogo de produtos</h1>
            <x-store.hero-carousel :banners="$banners" />
        @else
            <div class="store-container ct-hero-inner">
                <p class="ct-kicker">Bem-vindo à {{ $storeName }}</p>
                <h1>Novas possibilidades para suas compras.</h1>
                <p>Encontre produtos selecionados para o dia a dia e escolha com tranquilidade.</p>
                <a href="{{ route('store.products') }}" class="store-button">Ver produtos <x-heroicon-o-arrow-right class="size-5" /></a>
            </div>
        @endif
    </div>
    <div class="store-container ct-home-body">
        <div class="ct-benefits"><span><x-heroicon-o-squares-2x2 class="size-6" /> Catálogo completo</span><span><x-heroicon-o-chat-bubble-left-right class="size-6" /> Atendimento acessível</span><span><x-heroicon-o-shield-check class="size-6" /> Compra com segurança</span></div>
        <section id="categorias" class="ct-section">
            <div class="ct-section-heading"><div><p class="ct-kicker">Seleção para você</p><h2>Explore por categoria</h2></div><a href="{{ route('store.products') }}">Ver catálogo <span aria-hidden="true">→</span></a></div>
            <div class="ct-categories ct-categories-image">
                @forelse($categories as $category)<a href="{{ route('store.products',['categoria'=>$category->slug]) }}" class="ct-category">
                    <span class="ct-category-picture">@if($category->image_path)<img src="{{ Storage::disk('public')->url($category->image_path) }}" alt="" loading="lazy" width="320" height="270">@else<x-heroicon-o-squares-2x2 class="size-10" />@endif</span><strong>{{ $category->name }}</strong>
                </a>@empty<p>As categorias estarão disponíveis em breve.</p>@endforelse
            </div>
        </section>
        @if($featured->isNotEmpty())<section class="ct-section"><div class="ct-section-heading"><div><p class="ct-kicker">Nossa vitrine</p><h2>Produtos em destaque</h2></div><a href="{{ route('store.products') }}">Ver todos →</a></div><div class="store-product-grid">@foreach($featured as $product)<x-store.product-card :product="$product" />@endforeach</div></section>@endif
        @if($promotions->isNotEmpty())<section class="ct-section" data-section="promotions"><div class="ct-section-heading"><div><p class="ct-kicker">Oportunidades</p><h2>Ofertas especiais</h2></div><a href="{{ route('store.products',['promocao'=>1]) }}">Ver ofertas →</a></div><div class="store-product-grid">@foreach($promotions as $product)<x-store.product-card :product="$product" />@endforeach</div></section>@endif
        @if($latestBlogPosts->isNotEmpty())<section class="ct-section" data-section="latest-blog"><div class="ct-section-heading"><div><p class="ct-kicker">Fique por dentro</p><h2>Últimas do Blog</h2></div><a href="{{ route('store.blog.index') }}">Mais artigos →</a></div><div class="saba-blog-grid">@foreach($latestBlogPosts as $post)<x-store.blog-card :post="$post" />@endforeach</div></section>@endif
    </div>
</div>
