<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($definitions as $provider => $label)
            @php
                $integration = $integrations->get($provider);
                $implemented = app(\App\Infrastructure\Shipping\ShippingProviderRegistry::class)->isImplemented($provider);
                $connected = filled($integration?->connected_at) && filled($integration?->access_token);
            @endphp
            <x-filament::section :heading="$label">
                <div class="flex min-h-32 flex-col justify-between gap-6">
                    <p class="text-sm text-gray-600">
                        @if (! $implemented)
                            Não configurado · Em breve
                        @elseif ($provider === 'local_pickup')
                            {{ $integration?->is_enabled ? 'Disponível para retirada' : 'Retirada desativada' }}
                        @else
                            <span @class(['inline-block size-2 rounded-full mr-1', 'bg-success-500' => $connected, 'bg-gray-300' => ! $connected])></span>
                            {{ $connected ? 'Conta conectada' : 'Não conectado' }}
                        @endif
                    </p>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        @if ($implemented)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <span wire:replace>
                                    <x-filament::toggle
                                        :state="(int) (bool) $integration?->is_enabled"
                                        wire:key="provider-toggle-{{ $provider }}-{{ (int) (bool) $integration?->is_enabled }}"
                                        wire:click="toggleProvider('{{ $provider }}')"
                                        wire:loading.attr="disabled"
                                        aria-label="Ativar {{ $label }}"
                                    />
                                </span>
                                Ativo
                            </label>
                            @if ($provider === 'melhor_envio')
                                {{ $this->configureMelhorEnvioAction }}
                            @elseif ($provider === 'local_pickup')
                                {{ $this->configurePickupAction }}
                            @endif
                        @else
                            <x-filament::button color="gray" disabled>Em breve</x-filament::button>
                        @endif
                    </div>
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
