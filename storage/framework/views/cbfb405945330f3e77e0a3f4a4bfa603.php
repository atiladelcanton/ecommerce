<?php
    $pageTitle = $title ? $title.' | '.$storeName : ($settings?->seo_title ?: $storeName);
    $pageDescription = $description ?: ($settings?->seo_description ?: 'Conheça o catálogo de máquinas e equipamentos para serigrafia da '.$storeName.'.');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="description" content="<?php echo e($pageDescription); ?>">
    <link rel="canonical" href="<?php echo e($canonical ?: url()->current()); ?>">
    <meta property="og:title" content="<?php echo e($pageTitle); ?>">
    <meta property="og:description" content="<?php echo e($pageDescription); ?>">
    <meta property="og:url" content="<?php echo e($canonical ?: url()->current()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:image" content="<?php echo e($image ?: asset('images/saba-logo.jpeg')); ?>">
    <link rel="icon" href="<?php echo e(asset('images/saba-logo.jpeg')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('fonts/filament/filament/inter/index.css')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body <?php echo e($attributes->class(['store-body'])); ?>>
    <a href="#conteudo" class="store-skip">Pular para o conteúdo</a>
    <?php if (isset($component)) { $__componentOriginalefa10d7f09dd66caefc246aaded599a7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalefa10d7f09dd66caefc246aaded599a7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.header','data' => ['categories' => $navigationCategories,'whatsappUrl' => $whatsappUrl,'storeName' => $storeName]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($navigationCategories),'whatsapp-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($whatsappUrl),'store-name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($storeName)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalefa10d7f09dd66caefc246aaded599a7)): ?>
<?php $attributes = $__attributesOriginalefa10d7f09dd66caefc246aaded599a7; ?>
<?php unset($__attributesOriginalefa10d7f09dd66caefc246aaded599a7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalefa10d7f09dd66caefc246aaded599a7)): ?>
<?php $component = $__componentOriginalefa10d7f09dd66caefc246aaded599a7; ?>
<?php unset($__componentOriginalefa10d7f09dd66caefc246aaded599a7); ?>
<?php endif; ?>
    <main id="conteudo"><?php echo e($slot); ?></main>
    <?php if (isset($component)) { $__componentOriginal8df39643ebb659fe6bccbbfaf5f96b2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8df39643ebb659fe6bccbbfaf5f96b2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.footer','data' => ['settings' => $settings,'storeName' => $storeName,'whatsappUrl' => $whatsappUrl]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['settings' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($settings),'store-name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($storeName),'whatsapp-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($whatsappUrl)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8df39643ebb659fe6bccbbfaf5f96b2f)): ?>
<?php $attributes = $__attributesOriginal8df39643ebb659fe6bccbbfaf5f96b2f; ?>
<?php unset($__attributesOriginal8df39643ebb659fe6bccbbfaf5f96b2f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8df39643ebb659fe6bccbbfaf5f96b2f)): ?>
<?php $component = $__componentOriginal8df39643ebb659fe6bccbbfaf5f96b2f; ?>
<?php unset($__componentOriginal8df39643ebb659fe6bccbbfaf5f96b2f); ?>
<?php endif; ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scriptConfig(); ?>

</body>
</html>
<?php /**PATH /var/www/html/resources/views/components/store/layout.blade.php ENDPATH**/ ?>