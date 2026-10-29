<div class="store-container ct-home ct-essencia-home">
    <div class="ct-editorial-hero">
        <div class="ct-editorial-copy"><span class="ct-kicker">{{ $storeName }}</span><h1>Encontre seu próximo favorito.</h1><p>Uma curadoria feita para deixar a escolha mais simples, bonita e especial.</p><a class="store-button" href="{{ route('store.products') }}">Explorar coleção <x-heroicon-o-arrow-up-right class="size-5" /></a></div>
        <div class="ct-editorial-media">
            @if($banners->isNotEmpty())<x-store.hero-carousel :banners="$banners" />
            @elseif($categories->first()?->image_path)<img src="{{ Storage::disk('public')->url($categories->first()->image_path) }}" alt="" width="900" height="660">
            @else<div class="ct-editorial-placeholder"><span>ESCOLHA • DESCUBRA • APROVEITE</span></div>@endif
        </div>
    </div>
    <section id="categorias" class="ct-section ct-editorial-categories"><div class="ct-section-heading"><div><span class="ct-kicker">Conheça nossas coleções</span><h2>Descubra por categoria</h2></div><a href="{{ route('store.products') }}">Todas as categorias ↗</a></div>
        <div class="ct-categories ct-categories-editorial">@forelse($categories as $category)<a class="ct-category" href="{{ route('store.products',['categoria'=>$category->slug]) }}"><span class="ct-category-picture">@if($category->image_path)<img src="{{ Storage::disk('public')->url($category->image_path) }}" alt="" loading="lazy" width="550" height="560">@else<x-heroicon-o-squares-2x2 class="size-10" />@endif</span><strong>{{ $category->name }} <span aria-hidden="true">↗</span></strong></a>@empty<p>As categorias estarão disponíveis em breve.</p>@endforelse</div>
    </section>
    @if($featured->isNotEmpty())<section class="ct-section"><div class="ct-section-heading"><div><span class="ct-kicker">Escolhas especiais</span><h2>Nossa seleção</h2></div><a href="{{ route('store.products') }}">Ver coleção ↗</a></div><div class="store-product-grid">@foreach($featured as $product)<x-store.product-card :product="$product" />@endforeach</div></section>@endif
    <section class="ct-editorial-statement"><p>Um jeito mais leve de encontrar o que faz sentido para você.</p><a href="{{ route('store.about') }}">Conheça a nossa história →</a></section>
    @if($promotions->isNotEmpty())<section class="ct-section" data-section="promotions"><div class="ct-section-heading"><div><span class="ct-kicker">Oportunidades</span><h2>Ofertas selecionadas</h2></div><a href="{{ route('store.products',['promocao'=>1]) }}">Ver ofertas ↗</a></div><div class="store-product-grid">@foreach($promotions as $product)<x-store.product-card :product="$product" />@endforeach</div></section>@endif
    @if($latestBlogPosts->isNotEmpty())<section class="ct-section" data-section="latest-blog"><div class="ct-section-heading"><div><span class="ct-kicker">Inspiração</span><h2>Últimas do Blog</h2></div><a href="{{ route('store.blog.index') }}">Ler artigos ↗</a></div><div class="saba-blog-grid">@foreach($latestBlogPosts as $post)<x-store.blog-card :post="$post" />@endforeach</div></section>@endif
</div>
