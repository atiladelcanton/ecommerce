<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['availability']));

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

foreach (array_filter((['availability']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['store-availability', 'store-available' => $availability === \App\Domain\Catalog\Enums\ProductAvailability::IMMEDIATE, 'store-on-demand' => $availability === \App\Domain\Catalog\Enums\ProductAvailability::ON_DEMAND, 'store-unavailable' => $availability === \App\Domain\Catalog\Enums\ProductAvailability::UNAVAILABLE]); ?>"><span aria-hidden="true">●</span> <?php echo e($availability->label()); ?></span>
<?php /**PATH /var/www/html/resources/views/components/store/availability-badge.blade.php ENDPATH**/ ?>