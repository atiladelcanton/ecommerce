<x-store.layout title="Checkout">
    <div class="store-container py-8 pb-20">
        <x-store.breadcrumb :items="[['label' => 'Carrinho', 'url' => route('store.cart')], ['label' => 'Checkout']]" />
        <h1 class="store-title mt-4 mb-8">Finalizar pedido</h1>
        <livewire:store.checkout-page />
    </div>
</x-store.layout>
