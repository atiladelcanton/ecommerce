<?php
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    if ($lastPage <= 7) {
        $pages = range(1, $lastPage);
    } elseif ($currentPage <= 3) {
        $pages = [...range(1, 4), $lastPage];
    } elseif ($currentPage >= $lastPage - 2) {
        $pages = [1, ...range($lastPage - 3, $lastPage)];
    } else {
        $pages = array_values(array_unique([1, ...range($currentPage - 2, $currentPage + 2), $lastPage]));
    }
    $mobileStart = max(1, min($currentPage - 1, $lastPage - 2));
    $mobileEnd = min($lastPage, $mobileStart + 2);
    $previousNumber = null;
?>

<div class="storefront-pagination" x-data="{
    scrollToGrid() {
        this.$nextTick(() => document.getElementById('store-products-grid')?.scrollIntoView({
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
            block: 'start'
        }));
    }
}">
    <p class="storefront-pagination-summary" role="status">Mostrando <?php echo e($paginator->firstItem() ?? 0); ?>–<?php echo e($paginator->lastItem() ?? 0); ?> de <?php echo e($paginator->total()); ?> <?php echo e($paginator->total() === 1 ? 'produto' : 'produtos'); ?></p>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->hasPages()): ?>
        <div class="storefront-pagination-navigation">
            <p class="storefront-pagination-mobile-status">Página <?php echo e($currentPage); ?> de <?php echo e($lastPage); ?></p>
            <nav aria-label="Paginação de produtos">
                <button type="button" class="storefront-pagination-bullet" aria-label="Página anterior"
                    <?php if($paginator->onFirstPage()): echo 'disabled'; endif; ?>
                    <?php if(!$paginator->onFirstPage()): ?>
                        x-on:click="$wire.previousPage('<?php echo e($paginator->getPageName()); ?>').then(() => scrollToGrid())"
                        wire:loading.attr="disabled" wire:target="previousPage,nextPage,gotoPage"
                    <?php endif; ?>>
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-chevron-left'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4','aria-hidden' => 'true']); ?>
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
<?php endif; ?>
                </button>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($previousNumber !== null && $page > $previousNumber + 1): ?>
                        <span class="storefront-pagination-ellipsis storefront-pagination-desktop-only" aria-hidden="true">…</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php $desktopOnly = $page < $mobileStart || $page > $mobileEnd; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($page === $currentPage): ?>
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['storefront-pagination-bullet', 'storefront-pagination-desktop-only' => $desktopOnly]); ?>" aria-current="page" aria-label="Página <?php echo e($page); ?>, atual"><?php echo e($page); ?></span>
                    <?php else: ?>
                        <button type="button" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['storefront-pagination-bullet', 'storefront-pagination-desktop-only' => $desktopOnly]); ?>"
                            <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'storefront-'.e($paginator->getPageName()).'-'.e($page).''; ?>wire:key="storefront-<?php echo e($paginator->getPageName()); ?>-<?php echo e($page); ?>"
                            x-on:click="$wire.gotoPage(<?php echo e($page); ?>, '<?php echo e($paginator->getPageName()); ?>').then(() => scrollToGrid())"
                            wire:loading.attr="disabled" wire:target="previousPage,nextPage,gotoPage"
                            aria-label="Ver página <?php echo e($page); ?>"><?php echo e($page); ?></button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php $previousNumber = $page; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                <button type="button" class="storefront-pagination-bullet" aria-label="Próxima página"
                    <?php if(!$paginator->hasMorePages()): echo 'disabled'; endif; ?>
                    <?php if($paginator->hasMorePages()): ?>
                        x-on:click="$wire.nextPage('<?php echo e($paginator->getPageName()); ?>').then(() => scrollToGrid())"
                        wire:loading.attr="disabled" wire:target="previousPage,nextPage,gotoPage"
                    <?php endif; ?>>
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-chevron-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4','aria-hidden' => 'true']); ?>
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
<?php endif; ?>
                </button>
            </nav>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/atila/projects/saba-ecommerce-starter/resources/views/pagination/storefront.blade.php ENDPATH**/ ?>