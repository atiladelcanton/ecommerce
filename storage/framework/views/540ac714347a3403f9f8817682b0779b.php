<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'label', 'type' => 'text', 'autocomplete' => null, 'inputmode' => null, 'maxlength' => 255, 'required' => true, 'mask' => null, 'blur' => false, 'readonly' => false]));

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

foreach (array_filter((['name', 'label', 'type' => 'text', 'autocomplete' => null, 'inputmode' => null, 'maxlength' => 255, 'required' => true, 'mask' => null, 'blur' => false, 'readonly' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<div <?php echo e($attributes); ?>>
    <label for="checkout-<?php echo e($name); ?>" class="store-label"><?php echo e($label); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$required): ?><span class="font-normal text-store-muted"> (opcional)</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></label>
    <input id="checkout-<?php echo e($name); ?>" name="<?php echo e($name); ?>" type="<?php echo e($type); ?>" <?php if($blur): ?> wire:model.live.blur="form.<?php echo e($name); ?>" <?php else: ?> wire:model="form.<?php echo e($name); ?>" <?php endif; ?> class="store-input disabled:bg-slate-50 disabled:text-slate-500"
        <?php if($mask): ?> data-saba-mask="<?php echo e($mask); ?>" x-init="$nextTick(() => { if (window.SabaMasks?.<?php echo e($mask); ?>) $el.value = window.SabaMasks.<?php echo e($mask); ?>($el.value) })" <?php endif; ?>
        <?php if($autocomplete): ?> autocomplete="<?php echo e($autocomplete); ?>" <?php endif; ?> <?php if($inputmode): ?> inputmode="<?php echo e($inputmode); ?>" <?php endif; ?>
        maxlength="<?php echo e($maxlength); ?>" <?php if($required): echo 'required'; endif; ?> <?php if($readonly): echo 'readonly'; endif; ?> aria-describedby="checkout-<?php echo e($name); ?>-error" <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>>
    <p id="checkout-<?php echo e($name); ?>-error" class="mt-2 text-sm text-store-red" <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> role="alert" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><?php echo e($message); ?><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
    <?php echo e($slot); ?>

</div>
<?php /**PATH /var/www/html/resources/views/components/store/checkout-field.blade.php ENDPATH**/ ?>