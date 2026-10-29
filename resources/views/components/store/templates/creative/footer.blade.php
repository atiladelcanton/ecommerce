@props(['template', 'settings', 'storeName', 'whatsappUrl', 'logoUrl' => null])
<footer class="ct-footer">
    <div class="store-container ct-footer-grid">
        <div class="ct-footer-brand">
            <a href="{{ route('home') }}"><img src="{{ $logoUrl ?: asset('images/saba-logo.jpeg') }}" alt="{{ $storeName }}" width="165" height="60"></a>
            <p>Encontre o que você procura. Um catálogo completo, uma compra simples.</p>
        </div>
        <div><h2>Explore</h2><nav aria-label="Menu institucional"><a href="{{ route('home') }}">Início</a><a href="{{ route('store.products') }}">Produtos</a><a href="{{ route('store.about') }}">Quem Somos</a>@if($settings?->blog_enabled)<a href="{{ route('store.blog.index') }}">Blog</a>@endif</nav></div>
        <div><h2>Sua compra</h2><nav aria-label="Sua conta e compras"><a href="{{ route('store.cart') }}">Meu carrinho</a><a href="{{ auth('customer')->check() ? route('customer.dashboard') : route('customer.login') }}">Minha conta</a><a href="{{ route('store.contact') }}">Atendimento</a></nav></div>
        <div><h2>Contato</h2><div class="ct-footer-links">
            @if($settings?->phone)<a href="tel:{{ preg_replace('/[^+0-9]/', '', $settings->phone) }}">{{ $settings->phone }}</a>@endif
            @if($settings?->email)<a href="mailto:{{ $settings->email }}">{{ $settings->email }}</a>@endif
            @if($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>@endif
            @foreach(['instagram_url'=>'Instagram','facebook_url'=>'Facebook','youtube_url'=>'YouTube'] as $field=>$label)
                @if($settings?->{$field})<a href="{{ $settings->{$field} }}" target="_blank" rel="noopener noreferrer">{{ $label }} ↗</a>@endif
            @endforeach
        </div></div>
    </div>
    <div class="store-container ct-footer-bottom"><span>© {{ date('Y') }} {{ $storeName }}. Todos os direitos reservados.</span>@if($settings?->cnpj)<span>CNPJ {{ $settings->cnpj }}</span>@endif</div>
</footer>
