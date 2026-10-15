<div class="mx-auto max-w-3xl space-y-6">
    <h1 class="store-title"><?php echo e($order->status === \App\Domain\Order\Enums\OrderStatus::CANCELLED ? 'Pedido cancelado' : ($order->payment_status === \App\Domain\Payment\Enums\PaymentStatus::APPROVED ? 'Pedido confirmado' : 'Revise e conclua seu pedido')); ?></h1>
    <p class="text-store-muted">Pedido <?php echo e($order->number); ?> · <?php echo e($order->customer_name); ?></p>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?><div class="store-cart-error" role="alert"><?php echo e($errors->first()); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <section class="rounded-xl border border-store-border bg-white p-6">
        <h2 class="font-semibold">Produtos</h2>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="flex justify-between gap-4 border-b border-store-border py-4">
                <div><p><?php echo e($item->product_name); ?></p><p class="text-sm text-store-muted"><?php echo e($item->variant_name); ?> <?php echo e($item->variant_value); ?></p><p class="text-sm"><?php echo e($item->quantity); ?> × <?php echo e(\App\Support\BrazilianMoney::display($item->unit_price)); ?></p></div>
                <strong><?php echo e(\App\Support\BrazilianMoney::display($item->total)); ?></strong>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </section>
    <section class="rounded-xl border border-store-border bg-white p-6">
        <h2 class="font-semibold">Entrega combinada</h2>
        <p class="mt-3"><?php echo e($order->carrier); ?> · <?php echo e($order->shipping_name); ?></p>
        <p class="mt-2 text-store-muted"><?php echo e($order->shipping_description); ?></p>
        <p class="mt-2 whitespace-pre-line"><?php echo e($order->latestShippingQuote->instructions); ?></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->address): ?><p class="mt-4 text-sm"><?php echo e($order->address->street); ?>, <?php echo e($order->address->number); ?> · <?php echo e($order->address->city); ?>/<?php echo e($order->address->state); ?> · CEP <?php echo e(\App\Support\BrazilianData::postalCode($order->address->postal_code)); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <p class="mt-3 text-sm text-store-muted">Cotação válida até <?php echo e($order->latestShippingQuote->expires_at->format('d/m/Y H:i')); ?>. A reserva inicial de estoque dura 24 horas; após esse prazo, a disponibilidade é conferida novamente.</p>
    </section>
    <section class="rounded-xl border border-store-border bg-white p-6">
        <dl class="space-y-3"><div class="flex justify-between"><dt>Produtos</dt><dd><?php echo e(\App\Support\BrazilianMoney::display($order->subtotal_cents)); ?></dd></div><div class="flex justify-between"><dt>Frete</dt><dd><?php echo e($order->formattedShipping()); ?></dd></div><div class="flex justify-between border-t border-store-border pt-3 text-lg font-bold"><dt>Total</dt><dd><?php echo e($order->formattedTotal()); ?></dd></div></dl>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === \App\Domain\Order\Enums\OrderStatus::CANCELLED): ?>
            <p class="mt-5 text-store-red">Este pedido foi cancelado. Entre em contato com a SABA.</p>
        <?php elseif($order->payment_status === \App\Domain\Payment\Enums\PaymentStatus::APPROVED): ?>
            <p class="mt-5 font-semibold text-store-green" role="status">Pagamento aprovado — ambiente de demonstração.</p>
        <?php elseif($order->status === \App\Domain\Order\Enums\OrderStatus::RECEIVED): ?>
            <p class="mt-5 text-sm text-store-muted">Pagamento de demonstração. Nenhuma cobrança real será realizada.</p>
            <button type="button" wire:click="confirmPayment" wire:loading.attr="disabled" class="store-button mt-5 w-full">Confirmar pedido e pagamento de teste</button>
        <?php else: ?>
            <p class="mt-5 text-store-red">Este pedido não está disponível para pagamento. Entre em contato com a SABA.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>
</div>
<?php /**PATH /home/atila/projects/saba-ecommerce-starter/resources/views/livewire/store/shipping-quote-checkout.blade.php ENDPATH**/ ?>