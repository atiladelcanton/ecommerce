@props(['product'])
<div class="product-detail-gallery" x-data="{ selected: 0 }" role="region" aria-label="Fotos de {{ $product->name }}">
    <div class="product-detail-gallery-stage">
        @forelse($product->images as $image)
            <img x-show="selected === {{ $loop->index }}" @if(!$loop->first) x-cloak loading="lazy" @else fetchpriority="high" @endif src="{{ Storage::disk('public')->url($image->path) }}" alt="{{ $image->alt_text ?: $product->name }}" width="800" height="800" class="size-full object-contain">
        @empty
            <div class="h-full flex flex-col items-center justify-center gap-4 text-store-muted"><x-heroicon-o-photo class="size-16 opacity-40" /><span>Imagem não disponível</span></div>
        @endforelse
    </div>
    @if($product->images->count() > 1)
        <div class="product-detail-thumbnails" aria-label="Miniaturas do produto">
            @foreach($product->images as $image)
                <button type="button" class="product-detail-thumbnail" :aria-pressed="selected === {{ $loop->index }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" @click="selected = {{ $loop->index }}" aria-label="Ver foto {{ $loop->iteration }} de {{ $product->name }}">
                    <img src="{{ Storage::disk('public')->url($image->path) }}" alt="" width="88" height="88" class="size-full object-contain" loading="lazy">
                </button>
            @endforeach
        </div>
    @endif
</div>
