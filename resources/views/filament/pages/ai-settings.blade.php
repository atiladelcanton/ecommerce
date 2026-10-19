<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
        <x-filament::section>
            <div class="space-y-1">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Status</p>
                <p class="text-lg font-semibold text-gray-950 dark:text-white">{{ $settingsRecord->isConfigured() ? 'Pronto para uso' : 'Não configurado' }}</p>
                <p class="text-sm text-gray-500">{{ $settingsRecord->provider === 'groq' ? 'Groq' : $settingsRecord->provider }} · {{ $settingsRecord->model }}</p>
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="space-y-1">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Gerações no mês</p>
                <p class="text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($usageCount, 0, ',', '.') }}</p>
                <p class="text-sm text-gray-500">{{ number_format($successfulCount, 0, ',', '.') }} concluídas</p>
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="space-y-1">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Tokens de entrada</p>
                <p class="text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($inputTokens, 0, ',', '.') }}</p>
                <p class="text-sm text-gray-500">Consumo informado pelo provedor</p>
            </div>
        </x-filament::section>
        <x-filament::section>
            <div class="space-y-1">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Tokens de saída</p>
                <p class="text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($outputTokens, 0, ',', '.') }}</p>
                <p class="text-sm text-gray-500">Textos gerados neste mês</p>
            </div>
        </x-filament::section>
    </div>

    <x-filament::section heading="Sobre o plano gratuito do Groq">
        <p class="text-sm leading-6 text-gray-600 dark:text-gray-300">
            O projeto usa a API do Groq diretamente, sem SDK adicional. Ele está preparado para desenvolvimento com o Free Tier; limites e condições da conta são definidos pelo próprio Groq. Quando o limite disponível for atingido, o assistente mostrará uma mensagem para aguardar e tentar novamente.
        </p>
        <a href="https://console.groq.com/keys" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex text-sm font-semibold text-primary-600 hover:underline">Criar ou consultar API Key no Groq</a>
    </x-filament::section>

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3">
            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="save">
                Salvar configurações
            </x-filament::button>
            <x-filament::button type="button" color="gray" wire:click="saveAndTest" wire:loading.attr="disabled" wire:target="saveAndTest">
                Salvar e testar conexão
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
