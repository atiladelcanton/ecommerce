@props(['settings', 'storeName', 'whatsappUrl'])
@if($whatsappUrl)
<section class="bg-store-soft border-y border-store-border">
    <div class="store-container py-12 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
        <div><p class="store-eyebrow">Vamos conversar</p><h2 class="store-title mt-2">Encontre o equipamento para sua produção.</h2><p class="mt-3 text-store-muted">Fale com a {{ $storeName }} e tire suas dúvidas sobre os produtos.</p></div>
        <a class="store-button shrink-0" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Falar pelo WhatsApp <x-heroicon-o-arrow-up-right class="size-4" /></a>
    </div>
</section>
@endif
<footer class="store-container pt-16 pb-6">
    <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
        <div><img src="{{ asset('images/saba-logo.jpeg') }}" alt="{{ $storeName }}" width="150" height="44" class="w-36"><p class="mt-5 max-w-xs text-sm leading-7 text-store-muted">Máquinas e equipamentos para serigrafia. Conheça os produtos e converse com nossa equipe.</p></div>
        <div><h2 class="font-semibold mb-5">Explore</h2><nav aria-label="Navegação do rodapé" class="flex flex-col gap-3 text-sm text-store-muted"><a href="{{ route('home') }}">Início</a><a href="{{ route('store.products') }}">Produtos</a><a href="{{ route('home') }}#categorias">Categorias</a></nav></div>
        <div><h2 class="font-semibold mb-5">Contato</h2><div class="flex flex-col gap-3 text-sm text-store-muted">
            @if($settings?->phone)<a href="tel:{{ preg_replace('/[^+0-9]/', '', $settings->phone) }}">{{ $settings->phone }}</a>@endif
            @if($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>@endif
            @if($settings?->email)<a class="break-words" href="mailto:{{ $settings->email }}">{{ $settings->email }}</a>@endif
            @if($settings?->city)<span>{{ $settings->city }}{{ $settings->state ? ' / '.$settings->state : '' }}</span>@endif
        </div></div>
        <div><h2 class="font-semibold mb-5">Acompanhe a SABA</h2><div class="flex flex-col gap-3 text-sm text-store-muted">
            @foreach(['instagram_url' => 'Instagram', 'facebook_url' => 'Facebook', 'youtube_url' => 'YouTube'] as $field => $label)
                @if($settings?->{$field})<a href="{{ $settings->{$field} }}" target="_blank" rel="noopener noreferrer">{{ $label }} ↗</a>@endif
            @endforeach
        </div></div>
    </div>
    <div class="border-t border-store-border mt-12 pt-6 text-xs text-store-muted flex flex-wrap justify-between gap-3"><span>© {{ date('Y') }} {{ $storeName }}</span>@if($settings?->cnpj)<span>CNPJ {{ $settings->cnpj }}</span>@endif</div>
</footer>
