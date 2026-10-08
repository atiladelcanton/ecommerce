@props(['product'])
<div x-data="{ selected: 0 }">
    <div class="aspect-square bg-store-soft rounded-xl overflow-hidden">
        @forelse($product->images as $image)
            <img x-show="selected === {{ $loop->index }}" @if(!$loop->first) x-cloak loading="lazy" @else fetchpriority="high" @endif src="{{ Storage::disk('public')->url($image->path) }}" alt="{{ $image->alt_text ?: $product->name }}" width="800" height="800" class="size-full object-contain p-6 sm:p-10">
        @empty <div class="h-full flex flex-col items-center justify-center gap-4 text-store-muted"><x-heroicon-o-photo class="size-16 opacity-40" /><span>Imagem não disponível</span></div> @endforelse
    </div>
    @if($product->images->count() > 1)
        <div class="flex gap-3 mt-4 overflow-x-auto pb-2" aria-label="Miniaturas do produto">
            @foreach($product->images as $image)<button class="shrink-0 rounded-lg border p-1 bg-store-soft" :class="selected === {{ $loop->index }} ? 'border-store-red' : 'border-store-border'" @click="selected = {{ $loop->index }}" :aria-pressed="selected === {{ $loop->index }}" aria-label="Ver foto {{ $loop->iteration }}"><img src="{{ Storage::disk('public')->url($image->path) }}" alt="" width="80" height="80" class="size-20 object-contain" loading="lazy"></button>@endforeach
        </div>
    @endif
</div>
