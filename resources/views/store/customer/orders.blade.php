<x-store.layout title="Meus pedidos">
    <div class="store-container store-account-page">
        <div class="store-account-heading"><div><p class="store-eyebrow">Área do cliente</p><h1 class="store-title mt-2">Meus pedidos</h1><p class="mt-2 text-sm text-store-muted">Histórico completo dos pedidos vinculados à sua conta.</p></div><a class="store-button-secondary" href="{{ route('customer.dashboard') }}">Minha conta</a></div>
        <x-store.customer-nav />
        <section class="mt-6 store-account-card">
            <div class="overflow-x-auto"><table class="store-account-table"><thead><tr><th>Pedido</th><th>Data</th><th>Status</th><th>Pagamento</th><th>Itens</th><th>Total</th><th></th></tr></thead><tbody>
            @forelse($orders as $order)<tr><td class="font-semibold">{{ $order->number }}</td><td>{{ $order->created_at->format('d/m/Y H:i') }}</td><td><span class="store-order-status store-order-status--{{ $order->status->value }}">{{ $order->status->label() }}</span></td><td><span class="store-payment-status store-payment-status--{{ $order->payment_status->value }}">{{ $order->payment_status->label() }}</span></td><td>{{ $order->items_count }}</td><td class="font-semibold">{{ $order->formattedTotal() }}</td><td><a class="store-text-link" href="{{ route('customer.order', $order->number) }}">Acompanhar</a></td></tr>@empty<tr><td colspan="7" class="py-10 text-center text-store-muted">Você ainda não possui pedidos.</td></tr>@endforelse
            </tbody></table></div>
            <div class="mt-6">{{ $orders->links('pagination.account') }}</div>
        </section>
    </div>
</x-store.layout>
