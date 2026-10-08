<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['product']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['product']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<article class="store-product-card group" data-product-id="<?php echo e($product->id); ?>">
    <a href="<?php echo e(route('store.product', $product->slug)); ?>" class="block h-full">
        <div class="relative aspect-square bg-store-soft overflow-hidden rounded-lg">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->images->first()): ?>
                <img src="<?php echo e(Storage::disk('public')->url($product->images->first()->path)); ?>" alt="<?php echo e($product->images->first()->alt_text ?: $product->name); ?>" loading="lazy" width="500" height="500" class="size-full object-contain p-5 transition-transform duration-300 motion-safe:group-hover:scale-105">
            <?php else: ?>
                <div class="flex h-full flex-col items-center justify-center gap-3 text-store-muted"><?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-photo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-10 opacity-40']); ?>
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
<?php endif; ?><span class="text-xs">Imagem não disponível</span></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->has_promotion): ?><span class="store-discount">-<?php echo e($product->discount_percentage); ?>%</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="pt-4"><p class="text-xs text-store-muted"><?php echo e($product->category->name); ?></p><h3 class="mt-2 font-medium leading-snug min-h-12"><?php echo e($product->name); ?></h3><div class="my-3"><?php if (isset($component)) { $__componentOriginal333abf93aa73838b921ca17ad4c08e7c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal333abf93aa73838b921ca17ad4c08e7c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.availability-badge','data' => ['availability' => $product->availability]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.availability-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['availability' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->availability)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal333abf93aa73838b921ca17ad4c08e7c)): ?>
<?php $attributes = $__attributesOriginal333abf93aa73838b921ca17ad4c08e7c; ?>
<?php unset($__attributesOriginal333abf93aa73838b921ca17ad4c08e7c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal333abf93aa73838b921ca17ad4c08e7c)): ?>
<?php $component = $__componentOriginal333abf93aa73838b921ca17ad4c08e7c; ?>
<?php unset($__componentOriginal333abf93aa73838b921ca17ad4c08e7c); ?>
<?php endif; ?></div><?php if (isset($component)) { $__componentOriginal067ed8b6e5ef0df3ae9aa247904fdf86 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal067ed8b6e5ef0df3ae9aa247904fdf86 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.price','data' => ['product' => $product]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal067ed8b6e5ef0df3ae9aa247904fdf86)): ?>
<?php $attributes = $__attributesOriginal067ed8b6e5ef0df3ae9aa247904fdf86; ?>
<?php unset($__attributesOriginal067ed8b6e5ef0df3ae9aa247904fdf86); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal067ed8b6e5ef0df3ae9aa247904fdf86)): ?>
<?php $component = $__componentOriginal067ed8b6e5ef0df3ae9aa247904fdf86; ?>
<?php unset($__componentOriginal067ed8b6e5ef0df3ae9aa247904fdf86); ?>
<?php endif; ?></div>
    </a>
</article>
<?php /**PATH /var/www/html/resources/views/components/store/product-card.blade.php ENDPATH**/ ?>