<x-store.layout title="Blog" description="Dicas, novidades e conteúdos sobre serigrafia e equipamentos." :canonical="route('store.blog.index')">
    <div class="store-container saba-blog-page">
        <nav class="saba-blog-breadcrumb" aria-label="Você está aqui"><a href="{{ route('home') }}">Início</a><span aria-hidden="true">/</span><span>Blog</span></nav>
        <header class="saba-blog-page-header">
            <p class="store-eyebrow">Novidades, conhecimento e inspiração</p>
            <h1>Blog</h1>
            <p>Acompanhe conteúdos, novidades e dicas da SABA.</p>
        </header>
        @if($posts->isNotEmpty())
            <div class="saba-blog-grid saba-blog-grid-index">
                @foreach($posts as $post)
                    <x-store.blog-card :post="$post" />
                @endforeach
            </div>
            @if($posts->hasPages())
                <nav class="saba-blog-pagination" aria-label="Páginas do blog">{{ $posts->links() }}</nav>
            @endif
        @else
            <div class="saba-blog-empty">
                <h2>Em breve, novas histórias por aqui.</h2>
                <p>Acompanhe o blog para conferir as próximas publicações.</p>
                <a href="{{ route('home') }}" class="store-button">Voltar para a loja</a>
            </div>
        @endif
    </div>
</x-store.layout>
