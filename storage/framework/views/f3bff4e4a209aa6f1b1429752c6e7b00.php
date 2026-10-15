<?php if (isset($component)) { $__componentOriginalbe28baaac5279f8581803b8157bf8af7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbe28baaac5279f8581803b8157bf8af7 = $attributes; } ?>
<?php $component = App\View\Components\Store\Layout::resolve(['title' => 'Pedido recebido'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Store\Layout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="store-container py-10 pb-20">
        <div class="mb-8 flex items-start gap-4">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-green-50 text-store-green"><?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-6','aria-hidden' => 'true']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?></span>
            <div><h1 class="store-title"><?php echo e($order->shipping_pricing_status === \App\Domain\Shipping\Enums\ShippingPricingStatus::AWAITING_QUOTE ? 'Solicitação de entrega recebida' : 'Pedido realizado com sucesso'); ?></h1><p class="mt-3 text-store-muted">Número do pedido: <strong class="text-store-ink"><?php echo e($order->number); ?></strong></p></div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(app()->environment(['local', 'development']) && $order->payments->contains('is_demo', true)): ?>
            <p class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-store-amber" role="status">Este pedido utilizou pagamento simulado.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_380px]">
            <section class="min-w-0 rounded-xl border border-store-border bg-white p-5 sm:p-7" aria-labelledby="confirmation-summary-heading">
                <h2 id="confirmation-summary-heading" class="mb-6 text-lg font-semibold">Resumo do pedido</h2>
                <ul class="divide-y divide-store-border">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li class="flex flex-wrap justify-between gap-3 py-4 text-sm">
                            <div class="min-w-0"><p class="font-semibold wrap-anywhere"><?php echo e($item->product_name); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->product_sku): ?><p class="mt-1 text-xs text-store-muted">SKU: <?php echo e($item->product_sku); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->variant_value): ?><p class="mt-1 text-xs text-store-muted"><?php echo e($item->variant_name); ?>: <?php echo e($item->variant_value); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <p class="mt-1 text-xs text-store-muted"><?php echo e($item->quantity); ?> × <?php echo e(\App\Support\BrazilianMoney::display($item->unit_price)); ?></p>
                            </div>
                            <span class="shrink-0 font-semibold"><?php echo e(\App\Support\BrazilianMoney::display($item->total)); ?></span>
                        </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>
                <dl class="mt-4 border-t border-store-border pt-4 text-sm">
                    <div class="flex justify-between gap-4 py-2"><dt>Subtotal</dt><dd><?php echo e(\App\Support\BrazilianMoney::display($order->subtotal_cents)); ?></dd></div>
                    <div class="flex justify-between gap-4 py-2"><dt>Frete</dt><dd><?php echo e($order->formattedShipping()); ?></dd></div>
                    <div class="mt-3 flex justify-between gap-4 border-t border-store-border pt-4 text-lg font-semibold"><dt>Total</dt><dd><?php echo e($order->formattedTotal()); ?></dd></div>
                </dl>
            </section>
            <div class="grid min-w-0 gap-6">
                <section class="rounded-xl border border-store-border bg-white p-5 sm:p-6" aria-labelledby="confirmation-customer-heading">
                    <h2 id="confirmation-customer-heading" class="mb-4 font-semibold">Dados do cliente</h2>
                    <dl class="grid gap-3 text-sm wrap-anywhere">
                        <div><dt class="text-xs text-store-muted">Nome</dt><dd><?php echo e($order->customer_name); ?></dd></div>
                        <div><dt class="text-xs text-store-muted">E-mail</dt><dd><?php echo e($order->customer_email); ?></dd></div>
                        <div><dt class="text-xs text-store-muted">Telefone</dt><dd><?php echo e($order->customer_phone); ?></dd></div>
                        <div><dt class="text-xs text-store-muted">CPF/CNPJ</dt><dd><?php echo e($order->customer_document); ?></dd></div>
                    </dl>
                </section>
                <section class="rounded-xl border border-store-border bg-white p-5 sm:p-6" aria-labelledby="confirmation-delivery-heading">
                    <h2 id="confirmation-delivery-heading" class="mb-4 font-semibold">Endereço e forma de entrega</h2>
                    <address class="text-sm leading-6 not-italic wrap-anywhere">
                        <?php echo e($order->address->street); ?>, <?php echo e($order->address->number); ?><br>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->address->complement): ?><?php echo e($order->address->complement); ?><br><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php echo e($order->address->neighborhood); ?><br>
                        <?php echo e($order->address->city); ?> / <?php echo e($order->address->state); ?><br>
                        CEP: <?php echo e($order->address->postal_code); ?>

                    </address>
                    <p class="mt-4 text-sm font-semibold"><?php echo e($order->shipping_name); ?></p>
                    <p class="mt-1 text-xs leading-5 text-store-muted"><?php echo e($order->shipping_description); ?></p>
                </section>
                <section class="rounded-xl border border-store-border bg-store-soft p-5 sm:p-6" aria-labelledby="confirmation-payment-heading">
                    <h2 id="confirmation-payment-heading" class="mb-3 font-semibold">Pagamento</h2>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->payment_status === \App\Domain\Payment\Enums\PaymentStatus::APPROVED): ?>
                        <p class="text-sm text-store-green">Aprovado — ambiente de demonstração</p>
                    <?php else: ?>
                        <p class="text-sm text-store-muted">Aguardando cotação do frete. Nenhum pagamento foi realizado.</p>
                        <p class="mt-3 text-sm">Seus itens de pronta entrega ficam reservados por 24 horas. A SABA informará o frete e enviará o link para concluir a compra.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </section>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->shipping_pricing_status === \App\Domain\Shipping\Enums\ShippingPricingStatus::AWAITING_QUOTE): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shippingContactUrl): ?>
                <a href="<?php echo e($shippingContactUrl); ?>" target="_blank" rel="noopener noreferrer" class="store-button mt-6">Conversar sobre a entrega pelo WhatsApp</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="<?php echo e(route('store.products')); ?>" class="store-button-secondary">Continuar comprando</a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth('customer')->check()): ?>
                <a href="<?php echo e(route('customer.order', $order->number)); ?>" class="store-button">Acompanhar este pedido</a>
            <?php else: ?>
                <a href="<?php echo e(route('customer.register', ['email' => $order->customer_email])); ?>" class="store-button">Criar conta e acompanhar pedido</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbe28baaac5279f8581803b8157bf8af7)): ?>
<?php $attributes = $__attributesOriginalbe28baaac5279f8581803b8157bf8af7; ?>
<?php unset($__attributesOriginalbe28baaac5279f8581803b8157bf8af7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbe28baaac5279f8581803b8157bf8af7)): ?>
<?php $component = $__componentOriginalbe28baaac5279f8581803b8157bf8af7; ?>
<?php unset($__componentOriginalbe28baaac5279f8581803b8157bf8af7); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/store/order-confirmation.blade.php ENDPATH**/ ?>