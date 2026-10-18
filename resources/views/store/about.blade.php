<x-store.layout title="Quem Somos" description="Conheça a SABA e nossos produtos e soluções para serigrafia." :canonical="route('store.about')">
    <div class="store-container store-institutional-page">
        <x-store.breadcrumb :items="[['label' => 'Quem Somos']]" />
        <section class="store-institutional-intro">
            <p class="store-eyebrow">SABA · Equipamentos serigráficos</p>
            <h1>{{ $content['title'] }}</h1>
            <p>{{ $content['introduction'] }}</p>
        </section>
        <div class="store-institutional-grid">
            @foreach($content['sections'] as $section)
                <section class="store-institutional-card">
                    <h2>{{ $section['title'] }}</h2>
                    <p>{{ $section['text'] }}</p>
                </section>
            @endforeach
        </div>
        <div class="store-institutional-actions">
            <a class="store-button" href="{{ route('store.products') }}">Conhecer o catálogo <x-heroicon-o-arrow-right class="size-4" /></a>
            <a class="store-text-link" href="{{ route('store.contact') }}">Fale com a SABA →</a>
        </div>
    </div>
</x-store.layout>
