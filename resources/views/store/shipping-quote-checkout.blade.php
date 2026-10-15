<x-store.layout title="Concluir pedido">
    <div class="store-container py-10 pb-20">
        <livewire:store.shipping-quote-checkout :number="$number" :revision="$revision" :signed-url="$signedUrl" />
    </div>
</x-store.layout>
