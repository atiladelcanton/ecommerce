@props(['product', 'large' => false])
<div class="store-price {{ $large ? 'store-price-large' : '' }}">
    @if($product->has_promotion)
        <span class="block text-xs text-store-muted">De <del>{{ \App\Support\BrazilianMoney::display($product->regular_price) }}</del></span>
        <span class="font-semibold text-store-red"><span class="sr-only">Por </span>{{ \App\Support\BrazilianMoney::display($product->final_price) }}</span>
    @else
        <span class="font-semibold">{{ \App\Support\BrazilianMoney::display($product->final_price) }}</span>
    @endif
</div>
