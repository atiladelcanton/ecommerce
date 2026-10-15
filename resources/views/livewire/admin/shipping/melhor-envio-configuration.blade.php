<div class="space-y-5">
    <x-filament::tabs label="Configurações do Melhor Envio">
        @foreach (['connection' => 'Conexão', 'carriers' => 'Transportadoras e serviços', 'agencies' => 'Unidades de postagem', 'operation' => 'Operação'] as $key => $label)
            <x-filament::tabs.item :active="$tab === $key" wire:click="selectTab('{{ $key }}')">
                {{ $label }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    @error('shipping')
        <p role="alert" class="text-sm text-danger-600">{{ $message }}</p>
    @enderror

    @if ($tab === 'connection')
        <x-filament::section heading="Conta Melhor Envio">
            <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-gray-500">Status OAuth</dt><dd class="font-medium text-gray-950">{{ $connected ? 'Conta conectada' : 'Não conectado' }} · {{ $environmentLabel }}</dd></div>
                <div><dt class="text-gray-500">Token válido até</dt><dd class="text-gray-950">{{ $expiresAt?->format('d/m/Y H:i') ?? 'Sem autorização' }}</dd></div>
                <div class="min-w-0"><dt class="text-gray-500">Callback</dt><dd class="break-all text-gray-700">{{ $callback }}</dd></div>
                <div class="min-w-0"><dt class="text-gray-500">Webhook</dt><dd class="break-all text-gray-700">{{ $webhook }}</dd></div>
            </dl>
        </x-filament::section>
        {{ $this->connectionForm }}
        <div class="flex flex-wrap gap-3">
            <x-filament::button wire:click="saveConnection" wire:loading.attr="disabled">Salvar conexão</x-filament::button>
            <x-filament::button color="gray" wire:click="connect" wire:loading.attr="disabled">{{ $connected ? 'Reconectar' : 'Conectar conta' }}</x-filament::button>
            @if ($connected)
                <x-filament::button color="gray" wire:click="testConnection" wire:loading.attr="disabled">Testar conexão</x-filament::button>
                <x-filament::button color="danger" outlined wire:click="disconnect" wire:confirm="Desconectar a conta e desativar o Melhor Envio no checkout?" wire:loading.attr="disabled">Desconectar</x-filament::button>
            @endif
        </div>
    @elseif ($tab === 'carriers')
        <x-filament::section heading="Transportadoras e serviços" description="Os serviços habilitados aparecem no checkout quando a integração estiver ativa.">
            <x-filament::button wire:click="syncCarriers" :disabled="! $connected" wire:loading.attr="disabled">Sincronizar transportadoras</x-filament::button>
        </x-filament::section>
        @forelse ($carriers as $carrier)
            <x-filament::section :heading="$carrier->name" wire:key="carrier-{{ $carrier->id }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-gray-600">
                        @php($activeServices = $carrier->services->where('is_enabled', true)->where('is_available', true)->count())
                        {{ $carrier->is_enabled ? trans_choice(':count serviço ativo|:count serviços ativos', $activeServices) : 'Desativada' }}
                        @if (! $carrier->is_available) · Indisponível no catálogo @endif
                    </p>
                    <x-filament::button color="gray" wire:click="editCarrier({{ $carrier->id }})">Configurar</x-filament::button>
                </div>
                @if ($activeCarrierId === $carrier->id)
                    <div class="mt-5 space-y-4" wire:key="carrier-editor-{{ $carrier->id }}">
                        {{ $this->carrierForm }}
                        <div class="flex flex-wrap gap-3">
                            <x-filament::button wire:click="saveCarrier" wire:loading.attr="disabled">Salvar transportadora</x-filament::button>
                            <x-filament::button color="gray" wire:click="updateCarrierAgencies({{ $carrier->id }})">Atualizar unidades</x-filament::button>
                        </div>
                    </div>
                @endif
            </x-filament::section>
        @empty
            <x-filament::section><p class="text-sm text-gray-600">{{ $connected ? 'Sincronize o catálogo para configurar as transportadoras.' : 'Conecte a conta na aba Conexão para sincronizar o catálogo.' }}</p></x-filament::section>
        @endforelse
    @elseif ($tab === 'agencies')
        {{ $this->agencyForm }}
        <x-filament::button wire:click="syncAgencies" :disabled="! $connected" wire:loading.attr="disabled">Sincronizar unidades</x-filament::button>
        <x-filament::section heading="Unidades padrão por transportadora">
            <div class="divide-y divide-gray-100">
                @forelse ($carriers as $carrier)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div><p class="text-sm font-medium text-gray-950">{{ $carrier->name }}</p><p class="text-sm text-gray-600">{{ $carrier->defaultAgency?->name ?? 'Nenhuma unidade padrão' }}</p></div>
                        <x-filament::button color="gray" wire:click="editCarrier({{ $carrier->id }})">Selecionar unidade</x-filament::button>
                    </div>
                @empty
                    <p class="text-sm text-gray-600">Sincronize as transportadoras primeiro.</p>
                @endforelse
            </div>
        </x-filament::section>
    @elseif ($tab === 'operation')
        {{ $this->operationForm }}
        <x-filament::button wire:click="saveOperation" wire:loading.attr="disabled">Salvar operação</x-filament::button>
    @endif
</div>
