<div x-data class="pb-16">
    <div class="store-section-heading mt-4"><div><p class="store-eyebrow">Catálogo</p><h1 class="store-title mt-2">Produtos</h1></div></div>
    <div class="grid gap-10 lg:grid-cols-[240px_minmax(0,1fr)]">
        <aside class="hidden lg:block"><h2 class="font-semibold mb-6">Filtrar produtos</h2><?php if (isset($component)) { $__componentOriginal2b59c5726caf58923059773e71f434e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2b59c5726caf58923059773e71f434e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.filters','data' => ['categories' => $categories,'availabilities' => $availabilities,'prefix' => 'desktop']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.filters'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories),'availabilities' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($availabilities),'prefix' => 'desktop']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2b59c5726caf58923059773e71f434e9)): ?>
<?php $attributes = $__attributesOriginal2b59c5726caf58923059773e71f434e9; ?>
<?php unset($__attributesOriginal2b59c5726caf58923059773e71f434e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2b59c5726caf58923059773e71f434e9)): ?>
<?php $component = $__componentOriginal2b59c5726caf58923059773e71f434e9; ?>
<?php unset($__componentOriginal2b59c5726caf58923059773e71f434e9); ?>
<?php endif; ?></aside>
        <div class="min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-store-border mb-7">
                <p class="text-sm text-store-muted" role="status"><?php echo e($products->total()); ?> <?php echo e($products->total() === 1 ? 'produto encontrado' : 'produtos encontrados'); ?></p>
                <button type="button" class="store-button-secondary lg:hidden" @click="$refs.filters.showModal()"><?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-adjustments-horizontal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4']); ?>
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
<?php endif; ?> Filtros</button>
                <div class="flex items-center gap-2 text-sm"><label for="sort" class="sr-only">Ordenar produtos</label><select id="sort" wire:model.live="sort" class="store-input"><option value="">Mais recentes</option><option value="menor-preco">Menor preço</option><option value="maior-preco">Maior preço</option><option value="nome-az">Nome A-Z</option><option value="nome-za">Nome Z-A</option></select></div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search !== ''): ?><div class="mb-6 text-sm flex items-center gap-3">Resultados para “<?php echo e($search); ?>” <button class="store-text-link" wire:click="$set('search', '')">Remover busca</button></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <p wire:loading.delay class="text-sm text-store-muted mb-3" role="status">Atualizando produtos…</p>
            <div wire:loading.class="opacity-50" class="transition-opacity">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($products->count()): ?>
                    <div id="store-products-grid" class="store-product-grid store-catalog-grid"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'product-'.e($product->id).''; ?>wire:key="product-<?php echo e($product->id); ?>"><?php if (isset($component)) { $__componentOriginal27ce8c38763e676809739f9c69e6bc06 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal27ce8c38763e676809739f9c69e6bc06 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.product-card','data' => ['product' => $product]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal27ce8c38763e676809739f9c69e6bc06)): ?>
<?php $attributes = $__attributesOriginal27ce8c38763e676809739f9c69e6bc06; ?>
<?php unset($__attributesOriginal27ce8c38763e676809739f9c69e6bc06); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal27ce8c38763e676809739f9c69e6bc06)): ?>
<?php $component = $__componentOriginal27ce8c38763e676809739f9c69e6bc06; ?>
<?php unset($__componentOriginal27ce8c38763e676809739f9c69e6bc06); ?>
<?php endif; ?></div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></div>
                    <div class="mt-10"><?php echo e($products->links('pagination.storefront')); ?></div>
                <?php else: ?>
                    <div class="py-20 text-center bg-store-soft rounded-xl"><?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-magnifying-glass'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-10 mx-auto text-store-muted mb-5']); ?>
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
<?php endif; ?><h2 class="text-xl font-semibold">Nenhum produto encontrado.</h2><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search): ?><p class="text-store-muted mt-3">Nenhum resultado para '<?php echo e($search); ?>'.</p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><button class="store-button-secondary mt-6" wire:click="clearFilters">Limpar filtros</button></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
    <dialog x-ref="filters" class="store-dialog" aria-labelledby="filter-title">
        <div class="flex items-center justify-between mb-8"><h2 id="filter-title" class="text-xl font-semibold">Filtrar produtos</h2><button class="store-icon-btn" @click="$refs.filters.close()" aria-label="Fechar filtros"><?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-6']); ?>
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
<?php endif; ?></button></div>
        <?php if (isset($component)) { $__componentOriginal2b59c5726caf58923059773e71f434e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2b59c5726caf58923059773e71f434e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.filters','data' => ['categories' => $categories,'availabilities' => $availabilities,'prefix' => 'mobile']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.filters'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories),'availabilities' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($availabilities),'prefix' => 'mobile']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2b59c5726caf58923059773e71f434e9)): ?>
<?php $attributes = $__attributesOriginal2b59c5726caf58923059773e71f434e9; ?>
<?php unset($__attributesOriginal2b59c5726caf58923059773e71f434e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2b59c5726caf58923059773e71f434e9)): ?>
<?php $component = $__componentOriginal2b59c5726caf58923059773e71f434e9; ?>
<?php unset($__componentOriginal2b59c5726caf58923059773e71f434e9); ?>
<?php endif; ?>
        <button class="store-button w-full mt-8" @click="$refs.filters.close()">Ver produtos</button>
    </dialog>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/store/product-list.blade.php ENDPATH**/ ?>