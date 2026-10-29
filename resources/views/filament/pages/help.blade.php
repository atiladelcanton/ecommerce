<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('help/admin-help.css') }}">
    <div class="saba-help">
        <header class="saba-help-hero">
            <div>
                <p class="saba-help-eyebrow">CENTRAL DE AJUDA • MANUAL DO LOJISTA</p>
                <h2>Encontre uma resposta e siga o passo a passo</h2>
                <p>Guias práticos de cadastro, vendas, pagamentos, frete, etiquetas, blog e inteligência artificial. Somente leitura; nada nesta página altera sua loja.</p>
            </div>
            <span class="saba-help-count">{{ count($helpTopics) }} tutoriais</span>
        </header>

        <form method="GET" action="{{ \App\Filament\Pages\Help::getUrl() }}" class="saba-help-search" role="search">
            <label for="saba-help-query">Buscar no manual (inclusive dentro dos artigos)</label>
            <div class="saba-help-search-controls">
                <input id="saba-help-query" type="search" name="q" value="{{ $searchTerm }}" placeholder="Ex.: emitir etiqueta, cupom, cadastrar produto..." maxlength="90" autocomplete="off">
                <button type="submit">Pesquisar</button>
                @if($searchTerm !== '')<a href="{{ \App\Filament\Pages\Help::getUrl() }}">Limpar</a>@endif
            </div>
        </form>

        @if($searchTerm !== '')
            <section class="saba-help-results" aria-label="Resultados da pesquisa">
                <h3>{{ count($searchResults) }} {{ count($searchResults) === 1 ? 'resultado encontrado' : 'resultados encontrados' }} para “{{ $searchTerm }}”</h3>
                @forelse($searchResults as $slug => $topic)
                    <a href="{{ \App\Filament\Pages\Help::getUrl(['topico' => $slug]) }}" class="saba-help-result">
                        <strong>{{ $topic['title'] }}</strong><small>{{ $topic['summary'] }}</small>
                    </a>
                @empty
                    <p>Não encontramos esse termo. Tente outra palavra, por exemplo “etiqueta”, “frete”, “Asaas”, “banner” ou “produto”.</p>
                @endforelse
            </section>
        @else
            <div class="saba-help-layout">
                <nav class="saba-help-nav" aria-label="Tópicos de ajuda">
                    @php($lastGroup = null)
                    @foreach($helpTopics as $slug => $topic)
                        @if($lastGroup !== $topic['group'])
                            <p class="saba-help-nav-group">{{ $topic['group'] }}</p>
                            @php($lastGroup = $topic['group'])
                        @endif
                        <a @class(['saba-help-nav-link', 'is-current' => $selectedTopic === $slug])
                           @if($selectedTopic === $slug) aria-current="page" @endif
                           href="{{ \App\Filament\Pages\Help::getUrl(['topico' => $slug]) }}">{{ $topic['title'] }}</a>
                    @endforeach
                </nav>
                <article class="saba-help-article">
                    <div class="saba-help-article-topline">
                        <span>{{ $activeTopic['group'] }}</span>
                        <span>Guia operacional</span>
                    </div>
                    <h1>{{ $activeTopic['title'] }}</h1>
                    <p class="saba-help-lead">{{ $activeTopic['summary'] }}</p>
                    <div class="saba-help-article-tools">
                        <a href="{{ \App\Filament\Pages\Help::getUrl(['topico' => $selectedTopic]) }}" title="Link permanente deste tutorial">Link do tutorial</a>
                        <button type="button" onclick="window.print()" aria-label="Imprimir ou salvar tutorial em PDF">Imprimir / Salvar PDF</button>
                    </div>
                    @if(count($helpHeadings) > 1)
                        <nav class="saba-help-toc" aria-label="Índice deste tutorial">
                            <strong>Neste tutorial</strong>
                            <ul>
                                @foreach($helpHeadings as $heading)
                                    <li @class(['is-subheading' => $heading['level'] === 3])>
                                        <a href="#{{ $heading['id'] }}">{{ $heading['title'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </nav>
                    @endif
                    <div class="saba-help-content">{!! $helpHtml !!}</div>
                    <footer class="saba-help-foot">
                        <p><strong>Precisa de outro assunto?</strong> Avance para o próximo tutorial ou consulte os guias relacionados no menu.</p>
                        <div class="saba-help-pagination">
                            @if($previousTopicSlug)
                                <a rel="prev" href="{{ \App\Filament\Pages\Help::getUrl(['topico' => $previousTopicSlug]) }}">← {{ $helpTopics[$previousTopicSlug]['title'] }}</a>
                            @endif
                            @if($nextTopicSlug)
                                <a rel="next" href="{{ \App\Filament\Pages\Help::getUrl(['topico' => $nextTopicSlug]) }}">{{ $helpTopics[$nextTopicSlug]['title'] }} →</a>
                            @endif
                        </div>
                    </footer>
                </article>
            </div>
        @endif
    </div>
</x-filament-panels::page>
