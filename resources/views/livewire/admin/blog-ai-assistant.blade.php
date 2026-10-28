<div class="space-y-6">
    @if (! $configured)
        <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-sm text-warning-900" role="alert">
            <p class="font-semibold">A Inteligência Artificial ainda não está habilitada.</p>
            <p class="mt-1">Ative o provedor Groq e configure sua chave em Configurações → Inteligência Artificial.</p>
            <a href="{{ $settingsUrl }}" class="mt-3 inline-flex font-semibold underline">Abrir configurações de IA</a>
        </div>
    @else
        @if ($errorMessage)
            <div class="rounded-xl border border-danger-200 bg-danger-50 p-4 text-sm text-danger-900" role="alert">{{ $errorMessage }}</div>
        @endif

        @if ($suggestion === null)
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-gray-950">O que deseja fazer?</span>
                    <select wire:model="mode" class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-950">
                        <option value="generate">Escrever uma nova postagem</option>
                        <option value="improve">Melhorar o texto existente</option>
                    </select>
                </label>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                    <p class="font-semibold text-gray-950">Você tem o controle</p>
                    <p class="mt-1">A IA sugere título, resumo, artigo e SEO. Você escolhe o que aplicar e depois salva a postagem.</p>
                    <p class="mt-2">Capa, categoria, etiquetas, URL, situação e data de publicação permanecem inalteradas.</p>
                </div>
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-gray-950">Estilo</span>
                    <select wire:model="tone" class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-950">
                        <option value="didático">Didático</option>
                        <option value="informativo">Informativo</option>
                        <option value="institucional">Institucional</option>
                        <option value="descontraído">Descontraído</option>
                    </select>
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-gray-950">Tamanho do artigo</span>
                    <select wire:model="length" class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-950">
                        <option value="curto">Curto</option>
                        <option value="médio">Médio</option>
                        <option value="longo">Longo</option>
                    </select>
                </label>
            </div>

            <label class="block">
                <span class="mb-2 block text-sm font-medium text-gray-950">Tema e orientações (opcional ao melhorar um rascunho)</span>
                <textarea wire:model="brief" rows="6" maxlength="12000" class="block w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-950" placeholder="Ex.: Quero explicar como conservar quadros de serigrafia. Use as informações abaixo como referência, sem inventar dados.\n\nOu: Melhore a introdução, elimine repetições e deixe o texto mais fácil de entender."></textarea>
                <span class="mt-2 block text-xs text-gray-500">A IA não pesquisa fontes nem confirma dados. Forneça informações verificadas, especialmente para temas técnicos.</span>
            </label>

            <div class="flex justify-end">
                <x-filament::button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" icon="heroicon-o-sparkles">
                    <span wire:loading.remove wire:target="generate">Gerar sugestão</span>
                    <span wire:loading wire:target="generate">Gerando texto…</span>
                </x-filament::button>
            </div>
        @else
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-gray-950">Confira o resultado antes de aplicar</p>
                    <p class="text-sm text-gray-500">A sugestão não altera o artigo até você clicar em Aplicar. Depois, é preciso salvar a postagem.</p>
                </div>
                <x-filament::button type="button" color="gray" wire:click="resetSuggestion" size="sm">Gerar outra versão</x-filament::button>
            </div>
            <div class="max-h-[55vh] space-y-4 overflow-y-auto pr-2">
                @foreach (['title' => 'Título', 'excerpt' => 'Resumo', 'seo_title' => 'Título SEO', 'seo_description' => 'Descrição SEO'] as $field => $label)
                    @if (filled($suggestion[$field] ?? null))
                        <div class="rounded-xl border border-gray-200 p-4">
                            <label class="flex items-start gap-3">
                                <input type="checkbox" wire:model="applyFields.{{ $field }}" class="mt-1 rounded border-gray-300 text-primary-600">
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-gray-950">{{ $label }}</span>
                                    @if (filled($initial[$field] ?? null))
                                        <span class="mt-2 block text-xs font-semibold uppercase text-gray-500">Atual</span>
                                        <span class="mt-1 block whitespace-pre-line text-sm text-gray-600">{{ $initial[$field] }}</span>
                                    @endif
                                    <span class="mt-3 block text-xs font-semibold uppercase text-gray-500">Sugestão</span>
                                    <span class="mt-1 block whitespace-pre-line text-sm text-gray-950">{{ $suggestion[$field] }}</span>
                                </span>
                            </label>
                        </div>
                    @endif
                @endforeach
                @if (filled($suggestion['content'] ?? null))
                    <div class="rounded-xl border border-gray-200 p-4">
                        <label class="flex items-start gap-3">
                            <input type="checkbox" wire:model="applyFields.content" class="mt-1 rounded border-gray-300 text-primary-600">
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-gray-950">Artigo completo</span>
                                @if (filled($initial['content'] ?? null))
                                    <span class="mt-2 block text-xs font-semibold uppercase text-gray-500">Atual</span>
                                    <div class="prose prose-sm mt-1 max-w-none text-gray-600">{!! $currentHtml !!}</div>
                                @endif
                                <span class="mt-3 block text-xs font-semibold uppercase text-gray-500">Sugestão</span>
                                <div class="prose prose-sm mt-1 max-w-none text-gray-950">{!! $suggestedHtml !!}</div>
                            </span>
                        </label>
                    </div>
                @endif
            </div>
            <div class="flex flex-wrap justify-end gap-3">
                <x-filament::button type="button" color="gray" wire:click="resetSuggestion">Voltar</x-filament::button>
                <x-filament::button type="button" icon="heroicon-o-check" wire:click="apply" wire:loading.attr="disabled" wire:target="apply">Aplicar campos selecionados</x-filament::button>
            </div>
        @endif
    @endif
</div>
