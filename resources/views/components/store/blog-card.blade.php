@props(['post'])
<article class="saba-blog-card">
    <a href="{{ route('store.blog.show', $post->slug) }}" class="saba-blog-card-link" aria-label="Ler: {{ $post->title }}">
        <div class="saba-blog-card-cover">
            @if($post->cover_image)
                <img src="{{ Storage::disk('public')->url($post->cover_image) }}" alt="{{ $post->cover_alt ?: $post->title }}" loading="lazy" width="800" height="500">
            @else
                <div class="saba-blog-no-cover" aria-hidden="true"><span>Blog SABA</span></div>
            @endif
        </div>
        <div class="saba-blog-card-text">
            <p class="saba-blog-card-meta">
                @if($post->category)<span class="saba-blog-category">{{ $post->category }}</span>@endif
                <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('d/m/Y') }}</time>
            </p>
            <h3>{{ $post->title }}</h3>
            <p class="saba-blog-excerpt">{{ $post->summary() }}</p>
            <span class="saba-blog-read">Ler artigo <span aria-hidden="true">→</span></span>
        </div>
    </a>
</article>
