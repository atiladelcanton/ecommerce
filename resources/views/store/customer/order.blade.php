<x-store.layout title="Acompanhar pedido {{ $number }}">
    <div class="store-container store-account-page">
        <x-store.customer-nav />
        <div class="my-6"><a class="store-text-link" href="{{ route('customer.orders') }}">← Voltar aos pedidos</a></div>
        <livewire:store.customer-order-tracking :number="$number" />
    </div>
</x-store.layout>
