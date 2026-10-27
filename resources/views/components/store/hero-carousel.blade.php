@props(['banners'])
@if($banners->isNotEmpty())
    <section class="store-hero" x-data="storeCarousel({{ $banners->count() }})"
        :style="{ '--slide-offset': (direction * 20) + 'px' }"
        @mouseenter="hovered = true" @mouseleave="hovered = false"
        @focusin="focused = $event.target.matches(':focus-visible')" @focusout="focused = false"
        @touchstart.passive="touchStart($event)" @touchend.passive="touchEnd($event)" @touchcancel="touchCancel()"
        @click.capture="if (Date.now() < suppressClickUntil) { $event.preventDefault(); $event.stopPropagation(); }"
        aria-roledescription="carousel" aria-label="Destaques da loja">
        @foreach($banners as $banner)
            <div class="store-hero-slide"
                @if($loop->first) data-initial-active @else inert @endif
                :data-initial-active="null"
                :class="{ 'is-active': index === {{ $loop->index }}, 'is-leaving': previousIndex === {{ $loop->index }} }"
                aria-hidden="{{ $loop->first ? 'false' : 'true' }}" :aria-hidden="index !== {{ $loop->index }}" :inert="index !== {{ $loop->index }}"
                role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} de {{ $banners->count() }}">
                @if($banner->link)<a href="{{ $banner->link }}" class="block h-full">@endif
                <picture>
                    @if($banner->mobile_image)<source media="(max-width: 639px)" srcset="{{ Storage::disk('public')->url($banner->mobile_image) }}">@endif
                    <img src="{{ Storage::disk('public')->url($banner->desktop_image) }}" alt="{{ $banner->alt_text ?: $banner->title }}" class="h-full w-full object-contain" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif width="1600" height="600">
                </picture>
                @if($banner->link)</a>@endif
            </div>
        @endforeach
        @if($banners->count() > 1)
            <button type="button" class="store-carousel-control store-carousel-previous" @click="previous()" aria-label="Banner anterior"><x-heroicon-o-chevron-left class="size-4 sm:size-5" aria-hidden="true" /></button>
            <button type="button" class="store-carousel-control store-carousel-next" @click="next()" aria-label="Próximo banner"><x-heroicon-o-chevron-right class="size-4 sm:size-5" aria-hidden="true" /></button>
            <div class="store-carousel-indicators" aria-label="Escolher banner">
                @foreach($banners as $banner)
                    <button type="button" class="store-carousel-indicator" @click="goTo({{ $loop->index }})" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" :aria-pressed="index === {{ $loop->index }}" aria-label="Ver banner {{ $loop->iteration }}"></button>
                @endforeach
            </div>
            <p class="sr-only" :aria-live="hovered || focused || reducedMotion ? 'polite' : 'off'" aria-atomic="true" x-text="'Banner ' + (index + 1) + ' de ' + count"></p>
        @endif
    </section>
@endif
