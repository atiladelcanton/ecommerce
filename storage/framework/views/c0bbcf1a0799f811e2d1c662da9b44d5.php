<?php if (isset($component)) { $__componentOriginalbe28baaac5279f8581803b8157bf8af7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbe28baaac5279f8581803b8157bf8af7 = $attributes; } ?>
<?php $component = App\View\Components\Store\Layout::resolve(['title' => 'Minha conta'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Store\Layout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="store-container store-account-page">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?><div class="mb-6 rounded-lg border border-green-100 bg-green-50 p-4 text-sm text-store-green"><?php echo e(session('status')); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="store-account-heading">
            <div><p class="store-eyebrow">Área do cliente</p><h1 class="store-title mt-2">Olá, <?php echo e($customer->name); ?></h1><p class="mt-2 text-sm text-store-muted">Acompanhe seus pedidos e consulte o histórico de compras.</p></div>
            <form method="POST" action="<?php echo e(route('customer.logout')); ?>"><?php echo csrf_field(); ?><button class="store-button-secondary" type="submit">Sair</button></form>
        </div>
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
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <section class="store-account-stat"><span>Pedidos</span><strong><?php echo e($ordersCount); ?></strong><small>Total vinculado à sua conta</small></section>
            <section class="store-account-stat"><span>E-mail</span><strong class="text-base! wrap-anywhere"><?php echo e($customer->email); ?></strong><small>Conta de acesso</small></section>
            <section class="store-account-stat"><span>CPF/CNPJ</span><strong class="text-base!"><?php echo e(\App\Support\BrazilianData::document($customer->document)); ?></strong><small>Dados de faturamento</small></section>
        </div>
        <section class="mt-8 store-account-card">
            <div class="flex flex-wrap items-center justify-between gap-4"><h2 class="text-lg font-semibold">Pedidos recentes</h2><a class="store-text-link" href="<?php echo e(route('customer.orders')); ?>">Ver todos →</a></div>
            <div class="mt-4 overflow-x-auto"><table class="store-account-table"><thead><tr><th>Pedido</th><th>Data</th><th>Status</th><th>Itens</th><th>Total</th><th></th></tr></thead><tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><tr><td class="font-semibold"><?php echo e($order->number); ?></td><td><?php echo e($order->created_at->format('d/m/Y')); ?></td><td><span class="store-order-status store-order-status--<?php echo e($order->status->value); ?>"><?php echo e($order->status->label()); ?></span></td><td><?php echo e($order->items_count); ?></td><td><?php echo e($order->formattedTotal()); ?></td><td><a class="store-text-link" href="<?php echo e(route('customer.order', $order->number)); ?>">Acompanhar</a></td></tr><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?><tr><td colspan="6" class="py-10 text-center text-store-muted">Nenhum pedido vinculado à sua conta ainda.</td></tr><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody></table></div>
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
<?php /**PATH /var/www/html/resources/views/store/customer/dashboard.blade.php ENDPATH**/ ?>