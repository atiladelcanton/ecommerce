<?php if (isset($component)) { $__componentOriginalb7eb71b94feca6cf58d1f4956e3f9c69 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb7eb71b94feca6cf58d1f4956e3f9c69 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.hero-carousel','data' => ['banners' => $banners]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.hero-carousel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['banners' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($banners)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb7eb71b94feca6cf58d1f4956e3f9c69)): ?>
<?php $attributes = $__attributesOriginalb7eb71b94feca6cf58d1f4956e3f9c69; ?>
<?php unset($__attributesOriginalb7eb71b94feca6cf58d1f4956e3f9c69); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb7eb71b94feca6cf58d1f4956e3f9c69)): ?>
<?php $component = $__componentOriginalb7eb71b94feca6cf58d1f4956e3f9c69; ?>
<?php unset($__componentOriginalb7eb71b94feca6cf58d1f4956e3f9c69); ?>
<?php endif; ?><?php /**PATH /var/www/html/storage/framework/views/080332d0f8395b68546fea052d441089.blade.php ENDPATH**/ ?>