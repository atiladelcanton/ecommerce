<x-store.layout title="Minha conta">
    <div class="store-container store-account-page">
        @if(session('status'))<div class="mb-6 rounded-lg border border-green-100 bg-green-50 p-4 text-sm text-store-green">{{ session('status') }}</div>@endif
        <div class="store-account-heading">
            <div><p class="store-eyebrow">Área do cliente</p><h1 class="store-title mt-2">Olá, {{ $customer->name }}</h1><p class="mt-2 text-sm text-store-muted">Acompanhe seus pedidos e consulte o histórico de compras.</p></div>
            <form method="POST" action="{{ route('customer.logout') }}">@csrf<button class="store-button-secondary" type="submit">Sair</button></form>
        </div>
        <x-store.customer-nav />
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <section class="store-account-stat"><span>Pedidos</span><strong>{{ $ordersCount }}</strong><small>Total vinculado à sua conta</small></section>
            <section class="store-account-stat"><span>E-mail</span><strong class="text-base! wrap-anywhere">{{ $customer->email }}</strong><small>Conta de acesso</small></section>
            <section class="store-account-stat"><span>CPF/CNPJ</span><strong class="text-base!">{{ \App\Support\BrazilianData::document($customer->document) }}</strong><small>Dados de faturamento</small></section>
        </div>
        <section class="mt-8 store-account-card">
            <div class="flex flex-wrap items-center justify-between gap-4"><h2 class="text-lg font-semibold">Pedidos recentes</h2><a class="store-text-link" href="{{ route('customer.orders') }}">Ver todos →</a></div>
            <div class="mt-4 overflow-x-auto"><table class="store-account-table"><thead><tr><th>Pedido</th><th>Data</th><th>Status</th><th>Itens</th><th>Total</th><th></th></tr></thead><tbody>
            @forelse($orders as $order)<tr><td class="font-semibold">{{ $order->number }}</td><td>{{ $order->created_at->format('d/m/Y') }}</td><td><span class="store-order-status store-order-status--{{ $order->status->value }}">{{ $order->status->label() }}</span></td><td>{{ $order->items_count }}</td><td>{{ $order->formattedTotal() }}</td><td><a class="store-text-link" href="{{ route('customer.order', $order->number) }}">Acompanhar</a></td></tr>@empty<tr><td colspan="6" class="py-10 text-center text-store-muted">Nenhum pedido vinculado à sua conta ainda.</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </div>
</x-store.layout>
