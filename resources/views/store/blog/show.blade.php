<x-store.layout
    :title="$post->seo_title ?: $post->title"
    :description="$post->seo_description ?: $post->summary()"
    :canonical="route('store.blog.show', $post->slug)"
    og-type="article"
    :image="$post->cover_image ? Storage::disk('public')->url($post->cover_image) : null"
>
    <div class="store-container saba-blog-page saba-blog-article-page">
        <nav class="saba-blog-breadcrumb" aria-label="Você está aqui">
            <a href="{{ route('home') }}">Início</a><span aria-hidden="true">/</span>
            <a href="{{ route('store.blog.index') }}">Blog</a><span aria-hidden="true">/</span>
            <span aria-current="page">{{ $post->title }}</span>
        </nav>
        <article class="saba-blog-article">
            <header class="saba-blog-article-header">
                @if($post->category)<span class="saba-blog-category">{{ $post->category }}</span>@endif
                <h1>{{ $post->title }}</h1>
                @if($post->excerpt)<p class="saba-blog-lead">{{ $post->excerpt }}</p>@endif
                <div class="saba-blog-article-meta">
                    <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('d/m/Y') }}</time>
                    <span aria-hidden="true">·</span>
                    <span>{{ $post->author?->name ?: 'Equipe SABA' }}</span>
                </div>
            </header>
            @if($post->cover_image)
                <figure class="saba-blog-featured-image">
                    <img src="{{ Storage::disk('public')->url($post->cover_image) }}" alt="{{ $post->cover_alt ?: $post->title }}" width="1280" height="700">
                </figure>
            @endif
            <div class="saba-blog-content">{!! \App\Support\Catalog\ProductDescription::html($post->content) !!}</div>
            @if($post->tags)
                <footer class="saba-blog-tags" aria-label="Etiquetas da postagem">
                    @foreach($post->tags as $tag)<span>{{ $tag }}</span>@endforeach
                </footer>
            @endif
            <div class="saba-blog-back"><a href="{{ route('store.blog.index') }}">← Voltar para o blog</a></div>
        </article>
    </div>
</x-store.layout>
