<x-filament-panels::page>
    @php($customer = $this->record)

    <div class="grid gap-6 xl:grid-cols-12">
        <section class="fi-section p-6 xl:col-span-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Cliente</p>
                    <h2 class="mt-1 text-xl font-semibold text-zinc-950">{{ $customer->name }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">Cliente desde {{ $customer->created_at->format('d/m/Y') }}</p>
                </div>
                @if($customer->birth_date)
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-right">
                        <span class="block text-xs font-medium text-zinc-500">Data de nascimento</span>
                        <strong class="mt-1 block text-sm text-zinc-900">{{ $customer->birth_date->format('d/m/Y') }}</strong>
                    </div>
                @endif
            </div>

            <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                <div><dt class="text-xs font-medium uppercase tracking-wide text-zinc-500">E-mail</dt><dd class="mt-1 text-sm font-medium text-zinc-900">{{ $customer->email }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-zinc-500">Telefone</dt><dd class="mt-1 text-sm font-medium text-zinc-900">{{ \App\Support\BrazilianData::phone($customer->phone) }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-zinc-500">CPF/CNPJ</dt><dd class="mt-1 text-sm font-medium text-zinc-900">{{ \App\Support\BrazilianData::document($customer->document) }}</dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-zinc-500">Nascimento</dt><dd class="mt-1 text-sm font-medium text-zinc-900">{{ $customer->birth_date?->format('d/m/Y') ?? 'Não informado' }}</dd></div>
            </dl>
        </section>

        <section class="fi-section p-6 xl:col-span-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Resumo de compras</p>
            <div class="mt-5 grid grid-cols-2 gap-4">
                <div><span class="block text-xs text-zinc-500">Pedidos</span><strong class="mt-1 block text-2xl text-zinc-950">{{ number_format($ordersCount, 0, ',', '.') }}</strong></div>
                <div><span class="block text-xs text-zinc-500">Pedidos pagos</span><strong class="mt-1 block text-2xl text-zinc-950">{{ number_format($paidOrdersCount, 0, ',', '.') }}</strong></div>
                <div class="col-span-2 border-t border-zinc-200 pt-4"><span class="block text-xs text-zinc-500">Total em compras pagas</span><strong class="mt-1 block text-xl text-zinc-950">{{ \App\Support\BrazilianMoney::display($paidTotalCents) }}</strong></div>
                <div class="col-span-2"><span class="block text-xs text-zinc-500">Último pedido</span><strong class="mt-1 block text-sm text-zinc-900">{{ $lastOrderAt ? \Illuminate\Support\Carbon::parse($lastOrderAt)->format('d/m/Y H:i') : 'Sem pedidos' }}</strong></div>
            </div>
        </section>

        <section class="fi-section p-6 xl:col-span-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Endereços</p>
                <h2 class="mt-1 text-lg font-semibold text-zinc-950">Endereços cadastrados</h2>
            </div>
            <div class="mt-5 grid gap-4">
                @forelse($customer->addresses as $address)
                    <div class="rounded-xl border border-zinc-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <strong class="text-sm text-zinc-900">{{ $address->label ?: 'Endereço' }}</strong>
                            @if($address->is_default)<span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700">Principal</span>@endif
                        </div>
                        <p class="mt-2 text-sm leading-6 text-zinc-600">
                            {{ $address->street }}, {{ $address->number }}@if($address->complement) — {{ $address->complement }}@endif<br>
                            {{ $address->neighborhood }} · {{ $address->city }}/{{ $address->state }}<br>
                            CEP {{ \App\Support\BrazilianData::postalCode($address->postal_code) }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">Nenhum endereço cadastrado.</p>
                @endforelse
            </div>
        </section>

        <section class="fi-section p-6 xl:col-span-7">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Histórico</p>
                    <h2 class="mt-1 text-lg font-semibold text-zinc-950">Compras do cliente</h2>
                </div>
                <a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl() }}?tableSearch={{ urlencode($customer->email) }}" class="text-sm font-semibold text-red-600 hover:text-red-700">Ver todos em Pedidos</a>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500">
                        <tr><th class="pb-3 pr-4">Pedido</th><th class="pb-3 pr-4">Data</th><th class="pb-3 pr-4">Itens</th><th class="pb-3 pr-4">Status</th><th class="pb-3 pr-4 text-right">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($recentOrders as $order)
                            <tr>
                                <td class="py-3 pr-4"><a class="font-semibold text-red-600 hover:text-red-700" href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order]) }}">{{ $order->number }}</a></td>
                                <td class="py-3 pr-4 text-zinc-600">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 pr-4 text-zinc-600">{{ $order->items_count }}</td>
                                <td class="py-3 pr-4"><span class="font-medium text-zinc-800">{{ $order->status->label() }}</span></td>
                                <td class="py-3 pr-4 text-right font-semibold text-zinc-900">{{ $order->formattedTotal() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-zinc-500">Este cliente ainda não possui pedidos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
