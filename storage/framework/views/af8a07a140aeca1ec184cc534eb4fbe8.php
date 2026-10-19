<?php if (isset($component)) { $__componentOriginalbe28baaac5279f8581803b8157bf8af7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbe28baaac5279f8581803b8157bf8af7 = $attributes; } ?>
<?php $component = App\View\Components\Store\Layout::resolve(['title' => 'Meus pedidos'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Store\Layout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="store-container store-account-page">
        <div class="store-account-heading"><div><p class="store-eyebrow">Área do cliente</p><h1 class="store-title mt-2">Meus pedidos</h1><p class="mt-2 text-sm text-store-muted">Histórico completo dos pedidos vinculados à sua conta.</p></div><a class="store-button-secondary" href="<?php echo e(route('customer.dashboard')); ?>">Minha conta</a></div>
        <?php if (isset($component)) { $__componentOriginalb1b82393644a8f02fbf14470e9e44fd1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb1b82393644a8f02fbf14470e9e44fd1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.customer-nav','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.customer-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb1b82393644a8f02fbf14470e9e44fd1)): ?>
<?php $attributes = $__attributesOriginalb1b82393644a8f02fbf14470e9e44fd1; ?>
<?php unset($__attributesOriginalb1b82393644a8f02fbf14470e9e44fd1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb1b82393644a8f02fbf14470e9e44fd1)): ?>
<?php $component = $__componentOriginalb1b82393644a8f02fbf14470e9e44fd1; ?>
<?php unset($__componentOriginalb1b82393644a8f02fbf14470e9e44fd1); ?>
<?php endif; ?>
        <section class="mt-6 store-account-card">
            <div class="overflow-x-auto"><table class="store-account-table"><thead><tr><th>Pedido</th><th>Data</th><th>Status</th><th>Pagamento</th><th>Itens</th><th>Total</th><th></th></tr></thead><tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><tr><td class="font-semibold"><?php echo e($order->number); ?></td><td><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td><td><span class="store-order-status store-order-status--<?php echo e($order->status->value); ?>"><?php echo e($order->status->label()); ?></span></td><td><span class="store-payment-status store-payment-status--<?php echo e($order->payment_status->value); ?>"><?php echo e($order->payment_status->label()); ?></span></td><td><?php echo e($order->items_count); ?></td><td class="font-semibold"><?php echo e($order->formattedTotal()); ?></td><td><a class="store-text-link" href="<?php echo e(route('customer.order', $order->number)); ?>">Acompanhar</a></td></tr><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?><tr><td colspan="7" class="py-10 text-center text-store-muted">Você ainda não possui pedidos.</td></tr><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody></table></div>
            <div class="mt-6"><?php echo e($orders->links('pagination.account')); ?></div>
        </section>
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
<?php /**PATH /var/www/html/resources/views/store/customer/orders.blade.php ENDPATH**/ ?>