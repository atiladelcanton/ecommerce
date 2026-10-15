@props(['name', 'label', 'type' => 'text', 'autocomplete' => null, 'inputmode' => null, 'maxlength' => 255, 'required' => true, 'mask' => null, 'blur' => false, 'readonly' => false])
<div {{ $attributes }}>
    <label for="checkout-{{ $name }}" class="store-label">{{ $label }}@if(!$required)<span class="font-normal text-store-muted"> (opcional)</span>@endif</label>
    <input id="checkout-{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if($blur) wire:model.live.blur="form.{{ $name }}" @else wire:model="form.{{ $name }}" @endif class="store-input disabled:bg-slate-50 disabled:text-slate-500"
        @if($mask) data-saba-mask="{{ $mask }}" x-init="$nextTick(() => { if (window.SabaMasks?.{{ $mask }}) $el.value = window.SabaMasks.{{ $mask }}($el.value) })" @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif @if($inputmode) inputmode="{{ $inputmode }}" @endif
        maxlength="{{ $maxlength }}" @required($required) @readonly($readonly) aria-describedby="checkout-{{ $name }}-error" @error($name) aria-invalid="true" @enderror>
    <p id="checkout-{{ $name }}-error" class="mt-2 text-sm text-store-red" @error($name) role="alert" @enderror>@error($name){{ $message }}@enderror</p>
    {{ $slot }}
</div>
