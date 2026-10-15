<x-store.layout title="Carrinho">
    <div class="store-container store-cart-page">
        <x-store.breadcrumb :items="[['label' => 'Carrinho']]" />
        <h1 class="store-title">Meu carrinho</h1>
        <livewire:store.cart-page />
    </div>
</x-store.layout>
