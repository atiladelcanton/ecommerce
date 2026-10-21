<x-store.layout title="Situação do pagamento">
    <div class="store-container py-12 pb-20">
        <section class="mx-auto max-w-2xl rounded-xl border border-store-border bg-white p-6 sm:p-8">
            <h1 class="store-title">Pagamento do pedido {{ $order->number }}</h1>
            <p class="mt-4 text-store-muted">{{ $order->payment_status->label() }}.</p>
            @if($order->payment_status === \App\Domain\Payment\Enums\PaymentStatus::PENDING)
                <p class="mt-3 text-sm">A confirmação pode levar algum tempo. Os produtos com estoque controlado permanecem reservados enquanto a cobrança estiver pendente. Uma recusa ou expiração confirmada libera a reserva.</p>
            @elseif($order->payment_status === \App\Domain\Payment\Enums\PaymentStatus::APPROVED)
                <p class="mt-3 text-sm text-store-green">Recebemos a confirmação do pagamento.</p>
            @else
                <p class="mt-3 text-sm">A cobrança foi encerrada. Se necessário, entre em contato com a loja.</p>
            @endif
            @if(session('payment_notice'))
                <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm">{{ session('payment_notice') }}</p>
            @endif
            @if(in_array($order->payment_status, [\App\Domain\Payment\Enums\PaymentStatus::FAILED, \App\Domain\Payment\Enums\PaymentStatus::CANCELLED], true) && $order->status === \App\Domain\Order\Enums\OrderStatus::RECEIVED)
                <form method="POST" action="{{ route('store.order.payment-retry', $order->number) }}" class="mt-5">
                    @csrf
                    <button type="submit" class="store-button">Tentar pagar novamente</button>
                </form>
            @endif
            @if($order->payments->contains('needs_review', true))
                <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm">Pagamento em análise pela loja. Aguarde contato antes de retirar os produtos.</p>
            @endif
            <div class="mt-6 flex flex-wrap gap-3"><a class="store-button-secondary" href="{{ route('store.order.payment-status', $order->number) }}">Atualizar situação</a><a class="store-button" href="{{ route('store.products') }}">Continuar comprando</a></div>
        </section>
    </div>
</x-store.layout>
