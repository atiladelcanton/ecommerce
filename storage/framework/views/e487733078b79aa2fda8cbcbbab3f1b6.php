<?php if (isset($component)) { $__componentOriginalbe28baaac5279f8581803b8157bf8af7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbe28baaac5279f8581803b8157bf8af7 = $attributes; } ?>
<?php $component = App\View\Components\Store\Layout::resolve(['title' => 'Acompanhar pedido '.e($number).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Store\Layout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="store-container store-account-page">
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
        <div class="my-6"><a class="store-text-link" href="<?php echo e(route('customer.orders')); ?>">← Voltar aos pedidos</a></div>
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('store.customer-order-tracking', ['number' => $number]);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2562273821-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
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
<?php /**PATH /home/atila/projects/saba-ecommerce-starter/resources/views/store/customer/order.blade.php ENDPATH**/ ?>