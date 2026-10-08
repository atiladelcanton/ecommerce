@props(['banners'])
@if($banners->isNotEmpty())
<section class="store-hero" x-data="storeCarousel({{ $banners->count() }})" @mouseenter="hovered = true" @mouseleave="hovered = false" @focusin="focused = true" @focusout="focused = $el.contains($event.relatedTarget)" aria-roledescription="carousel" aria-label="Destaques da loja">
    @foreach($banners as $banner)
        <div x-show="index === {{ $loop->index }}" @if(!$loop->first) x-cloak @endif role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} de {{ $banners->count() }}" class="h-full">
            @if($banner->link)<a href="{{ $banner->link }}" class="block h-full">@endif
            <picture>
                @if($banner->mobile_image)<source media="(max-width: 639px)" srcset="{{ Storage::disk('public')->url($banner->mobile_image) }}">@endif
                <img src="{{ Storage::disk('public')->url($banner->desktop_image) }}" alt="{{ $banner->alt_text ?: $banner->title }}" class="h-full w-full object-cover" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif width="1600" height="600">
            </picture>
            @if($banner->link)</a>@endif
        </div>
    @endforeach
    @if($banners->count() > 1)
        <div class="absolute inset-x-0 bottom-5 flex items-center justify-center gap-3">
            <button class="store-carousel-control" @click="previous()" aria-label="Banner anterior">←</button>
            @foreach($banners as $banner)<button class="size-2.5 rounded-full bg-white ring-1 ring-black/10" :class="index === {{ $loop->index }} ? 'opacity-100' : 'opacity-50'" @click="index = {{ $loop->index }}" :aria-pressed="index === {{ $loop->index }}" aria-label="Mostrar banner {{ $loop->iteration }}"></button>@endforeach
            <button class="store-carousel-control" @click="next()" aria-label="Próximo banner">→</button>
            <button class="store-carousel-control text-xs" @click="paused = !paused" :aria-label="paused ? 'Reproduzir banners' : 'Pausar banners'" x-text="paused ? '▶' : 'Ⅱ'"></button>
        </div>
    @endif
</section>
@endif
