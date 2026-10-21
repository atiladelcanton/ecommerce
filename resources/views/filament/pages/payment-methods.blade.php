<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-filament::section heading="Asaas" icon="heroicon-o-credit-card">
            <p class="text-sm text-gray-600">Checkout hospedado com Pix e cartão de crédito. Pedidos confirmados pelos webhooks oficiais.</p>
            <p class="mt-2 text-sm font-semibold">{{ $integrations->contains('is_enabled', true) ? 'Ativo' : 'Aguardando configuração' }}</p>
        </x-filament::section>
        <x-filament::section heading="Mercado Pago" icon="heroicon-o-archive-box">
            <p class="text-sm text-gray-600">Desativado para novas compras. Código e registros financeiros anteriores preservados para eventual reativação futura.</p>
        </x-filament::section>
        <x-filament::section heading="Efí" icon="heroicon-o-clock">
            <p class="text-sm text-gray-600">Não implementado. Disponível para uma próxima etapa.</p>
        </x-filament::section>
    </div>
    <x-filament::section heading="Configurar Asaas" description="Ambiente, chave e webhook próprios. As credenciais gravadas ficam ocultas; deixe os campos em branco para manter as atuais.">
        <form wire:submit="save" class="space-y-5">
            <div class="grid gap-4 md:grid-cols-2">
                <label class="space-y-1 text-sm font-medium">Ambiente
                    <select wire:model.live="environment" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900">
                        <option value="sandbox">Testes (sandbox)</option><option value="production">Produção</option>
                    </select>
                </label>
                <label class="space-y-1 text-sm font-medium">Validade do checkout (minutos)
                    <input wire:model="expirationMinutes" type="number" min="10" max="1440" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900">
                    @error('expirationMinutes')<span class="text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="space-y-1 text-sm font-medium">API Key do Asaas (salva e oculta)
                    <input wire:model="accessToken" type="password" autocomplete="new-password" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900" placeholder="Preencha apenas para alterar">
                </label>
                <label class="space-y-1 text-sm font-medium">Token de autenticação dos Webhooks (mín. 32 caracteres)
                    <input wire:model="webhookSecret" type="password" autocomplete="new-password" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900" placeholder="Mesmo token configurado no Asaas">
                </label>
                <label class="space-y-1 text-sm font-medium">Parcelamento máximo no cartão
                    <input wire:model="maxInstallments" type="number" min="1" max="21" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900">
                </label>
            </div>
            <div class="flex flex-wrap gap-6 text-sm">
                <label class="flex items-center gap-2"><input wire:model="pix" type="checkbox"> Pix</label>
                <label class="flex items-center gap-2"><input wire:model="creditCard" type="checkbox"> Cartão de crédito</label>
                <label class="flex items-center gap-2"><input wire:model="isEnabled" type="checkbox"> Ativar Asaas no checkout</label>
            </div>
            <p class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">Webhook Asaas:<br><code class="break-all">{{ route('webhooks.payments.asaas') }}</code><br>Selecione os eventos <strong>CHECKOUT_CREATED, CHECKOUT_PAID, CHECKOUT_CANCELED, CHECKOUT_EXPIRED</strong>. Configure o mesmo token no Asaas e neste formulário. A reserva de estoque só é liberada após um evento terminal autenticado, nunca por um prazo local isolado.</p>
            <div class="flex flex-wrap gap-3">
                <x-filament::button type="submit" icon="heroicon-o-check">Salvar</x-filament::button>
                <x-filament::button type="button" color="gray" wire:click="testConnection" icon="heroicon-o-signal">Testar conexão salva</x-filament::button>
            </div>
        </form>
    </x-filament::section>
    <x-filament::section heading="Últimas cobranças Asaas" description="Ocorrências financeiras pendentes de análise permanecem visíveis mesmo se o gateway for desativado.">
        <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="border-b"><th class="p-3">Pedido</th><th class="p-3">Ambiente</th><th class="p-3">Situação</th><th class="p-3">Vencimento</th><th class="p-3">Operação</th></tr></thead><tbody>
            @forelse($recentPayments as $payment)
                <tr class="border-b"><td class="p-3 font-medium">{{ $payment->order?->number }}</td><td class="p-3">{{ $payment->environment }}</td>
                    <td class="p-3">{{ $payment->status->label() }} @if($payment->needs_review)<strong class="text-red-600">· Requer revisão</strong><span class="block text-xs">{{ $payment->review_reason }}</span>@endif</td>
                    <td class="p-3">{{ $payment->expires_at?->format('d/m/Y H:i') ?? '—' }}</td><td class="p-3">@if($payment->status === \App\Domain\Payment\Enums\PaymentStatus::PENDING && !str_starts_with($payment->external_id, 'initiating-'))<x-filament::button type="button" size="sm" color="danger" wire:click="cancelCheckout({{ $payment->id }})" wire:confirm="Solicitar cancelamento ao Asaas? O estoque só será liberado quando o Asaas confirmar.">Solicitar cancelamento</x-filament::button>@endif</td></tr>
            @empty <tr><td colspan="5" class="p-4 text-gray-500">Nenhuma cobrança Asaas registrada.</td></tr> @endforelse
        </tbody></table></div>
    </x-filament::section>
</x-filament-panels::page>
