<div class="space-y-6">
    @if (! $configured)
        <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-sm text-warning-900">
            <p class="font-semibold">Configure a Inteligência Artificial primeiro.</p>
            <p class="mt-1">Cadastre sua API Key gratuita do Groq e ative o assistente.</p>
            <a href="{{ $settingsUrl }}" class="mt-3 inline-flex font-semibold underline">Ir para configurações de IA</a>
        </div>
    @else
        @if ($errorMessage)
            <div class="rounded-xl border border-danger-200 bg-danger-50 p-4 text-sm text-danger-900" role="alert">
                {{ $errorMessage }}
            </div>
        @endif

        @if ($suggestion === null)
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-gray-950 dark:text-white">O que você quer fazer?</span>
                    <select wire:model="mode" class="fi-select-input block w-full rounded-lg border-gray-300 bg-white text-gray-950 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="generate">Criar textos para o cadastro</option>
                        <option value="improve">Melhorar os textos atuais</option>
                    </select>
                </label>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">
                    <p class="font-medium text-gray-950 dark:text-white">O que a IA pode alterar</p>
                    <p class="mt-1">Nome, descrição curta, descrição completa, SEO e sugestão de categoria.</p>
                    <p class="mt-2 font-medium text-gray-950 dark:text-white">O que ela nunca altera</p>
                    <p class="mt-1">Preço, custo, promoção, estoque, SKU, peso, dimensões e variações.</p>
                </div>
            </div>

            <label class="block">
                <span class="mb-2 block text-sm font-medium text-gray-950 dark:text-white">Informações adicionais sobre o produto</span>
                <textarea wire:model="brief" rows="6" maxlength="12000" class="block w-full rounded-lg border-gray-300 bg-white text-gray-950 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white" placeholder="Ex.: Nylon amarelo, 77 fios, vendido por metro, indicado para telas de serigrafia. Não invente outras características."></textarea>
                <span class="mt-2 block text-xs text-gray-500">Pode colar ficha técnica, observações, diferenciais reais ou apenas complementar o cadastro que já está preenchido.</span>
            </label>

            <div class="flex justify-end">
                <x-filament::button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" icon="heroicon-o-sparkles">
                    <span wire:loading.remove wire:target="generate">Gerar sugestões</span>
                    <span wire:loading wire:target="generate">Gerando…</span>
                </x-filament::button>
            </div>
        @else
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-gray-950 dark:text-white">Revise antes de aplicar</p>
                    <p class="text-sm text-gray-500">Nada é salvo no produto até você aplicar as sugestões e depois salvar o cadastro.</p>
                </div>
                <x-filament::button type="button" color="gray" wire:click="resetSuggestion" size="sm">Gerar novamente</x-filament::button>
            </div>

            <div class="space-y-4">
                @foreach ([
                    'name' => 'Nome',
                    'short_description' => 'Descrição curta',
                    'seo_title' => 'Título SEO',
                    'seo_description' => 'Descrição SEO',
                ] as $field => $label)
                    @if (filled($suggestion[$field] ?? null))
                        <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                            <label class="flex items-start gap-3">
                                <input type="checkbox" wire:model="applyFields.{{ $field }}" class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-gray-950 dark:text-white">{{ $label }}</span>
                                    @if (filled($initial[$field] ?? null))
                                        <span class="mt-2 block text-xs font-medium uppercase tracking-wide text-gray-400">Atual</span>
                                        <span class="mt-1 block text-sm text-gray-500">{{ strip_tags((string) $initial[$field]) }}</span>
                                    @endif
                                    <span class="mt-3 block text-xs font-medium uppercase tracking-wide text-gray-400">Sugestão</span>
                                    <span class="mt-1 block text-sm text-gray-950 dark:text-white">{{ strip_tags((string) $suggestion[$field]) }}</span>
                                </span>
                            </label>
                        </div>
                    @endif
                @endforeach

                @if (filled($suggestion['description'] ?? null))
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <label class="flex items-start gap-3">
                            <input type="checkbox" wire:model="applyFields.description" class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-gray-950 dark:text-white">Descrição completa</span>
                                @if (filled($initial['description'] ?? null))
                                    <span class="mt-2 block text-xs font-medium uppercase tracking-wide text-gray-400">Atual</span>
                                    <div class="prose prose-sm mt-1 max-w-none text-gray-500">{!! $currentDescription !!}</div>
                                @endif
                                <span class="mt-3 block text-xs font-medium uppercase tracking-wide text-gray-400">Sugestão</span>
                                <div class="prose prose-sm mt-1 max-w-none text-gray-950 dark:text-white">{!! $suggestedDescription !!}</div>
                            </span>
                        </label>
                    </div>
                @endif

                @if (filled($suggestion['category_id'] ?? null))
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <label class="flex items-start gap-3">
                            <input type="checkbox" wire:model="applyFields.category_id" class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                            <span>
                                <span class="block text-sm font-semibold text-gray-950 dark:text-white">Categoria sugerida</span>
                                <span class="mt-1 block text-sm text-gray-600 dark:text-gray-300">{{ $suggestion['category_name'] }}</span>
                            </span>
                        </label>
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                <x-filament::button type="button" color="gray" wire:click="resetSuggestion">Voltar</x-filament::button>
                <x-filament::button type="button" wire:click="apply" wire:loading.attr="disabled" wire:target="apply" icon="heroicon-o-check">
                    Aplicar selecionados ao formulário
                </x-filament::button>
            </div>
        @endif
    @endif
</div>
