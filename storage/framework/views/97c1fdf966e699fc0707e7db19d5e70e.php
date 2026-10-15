<div class="relative" x-data="storeSearch" @click.outside="open = false" @keydown.escape="open = false; active = -1" @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)" @keydown.enter="choose($event)">
    <form action="<?php echo e(route('store.products')); ?>" method="get" role="search">
        <label for="store-search" class="sr-only">Buscar produtos</label>
        <div class="relative"><input id="store-search" name="busca" type="search" wire:model.live.debounce.300ms="term" maxlength="100" placeholder="O que você procura?" class="store-input pr-12" autocomplete="off" role="combobox" aria-autocomplete="list" aria-controls="search-suggestions" :aria-expanded="open" :aria-activedescendant="active >= 0 ? 'search-option-' + active : null" @focus="open = true" @input="open = true; active = -1"><button type="submit" class="absolute right-3 top-2.5" aria-label="Buscar"><?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-magnifying-glass'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-5 text-store-muted']); ?>
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
    </form>
    <span wire:loading.delay wire:target="term" class="absolute right-0 top-full text-xs bg-white text-store-muted px-2 z-40" role="status">Buscando…</span>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(mb_strlen(trim($term)) >= 2): ?>
        <div x-show="open" x-cloak class="store-dropdown right-0 w-full min-w-0" id="search-suggestions" role="listbox" aria-label="Sugestões de produtos">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $suggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <a data-suggestion id="search-option-<?php echo e($loop->index); ?>" role="option" :aria-selected="active === <?php echo e($loop->index); ?>" :class="active === <?php echo e($loop->index); ?> ? 'bg-store-soft' : ''" href="<?php echo e(route('store.product', $product->slug)); ?>" class="flex items-center gap-3 p-3 rounded-md hover:bg-store-soft" @mouseenter="active = <?php echo e($loop->index); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->images->first()): ?><img class="size-12 object-contain bg-store-soft rounded" src="<?php echo e(Storage::disk('public')->url($product->images->first()->path)); ?>" alt="" loading="lazy" width="48" height="48"><?php else: ?><?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-photo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-10 shrink-0 text-store-muted']); ?>
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
<?php endif; ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="min-w-0"><span class="block text-sm font-medium"><?php echo e($product->name); ?></span><span class="block text-xs text-store-muted mt-1"><?php echo e($product->category->name); ?></span><span class="block text-xs mt-1 font-semibold"><?php echo e(\App\Support\BrazilianMoney::display($product->final_price)); ?></span></span>
                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?> <p class="p-3 text-sm text-store-muted" role="status">Nenhum resultado para '<?php echo e($term); ?>'.</p> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <a data-suggestion id="search-option-<?php echo e($suggestions->count()); ?>" role="option" :aria-selected="active === <?php echo e($suggestions->count()); ?>" class="block border-t border-store-border p-3 text-sm font-medium text-store-red" :class="active === <?php echo e($suggestions->count()); ?> ? 'bg-store-soft' : ''" href="<?php echo e(route('store.products', ['busca' => $term])); ?>">Ver todos os resultados para '<?php echo e($term); ?>' →</a>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/atila/projects/saba-ecommerce-starter/resources/views/livewire/store/search-autocomplete.blade.php ENDPATH**/ ?>