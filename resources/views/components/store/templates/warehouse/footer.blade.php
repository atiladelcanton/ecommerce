@props(['settings', 'storeName', 'whatsappUrl'])
<footer class="wh-footer">
    <div class="store-container wh-footer-grid">
        <div class="wh-footer-brand"><img src="{{ asset('images/saba-logo.jpeg') }}" width="155" height="54" alt="{{ $storeName }}"><p>Um catálogo completo para encontrar os produtos que você procura.</p></div>
        <div><h2>Institucional</h2><nav aria-label="Informações institucionais"><a href="{{ route('store.about') }}">Quem Somos</a><a href="{{ route('store.contact') }}">Contato</a>@if($settings?->blog_enabled)<a href="{{ route('store.blog.index') }}">Blog</a>@endif</nav></div>
        <div><h2>Minha conta</h2><nav aria-label="Conta e compras"><a href="{{ route('store.products') }}">Comprar produtos</a><a href="{{ route('store.products', ['promocao' => 1]) }}">Ofertas</a><a href="{{ auth('customer')->check() ? route('customer.dashboard') : route('customer.login') }}">Minha conta</a><a href="{{ route('store.cart') }}">Meu carrinho</a></nav></div>
        <div><h2>Atendimento</h2><div class="wh-footer-links">
            @if($settings?->phone)<a href="tel:{{ preg_replace('/[^+0-9]/', '', $settings->phone) }}">{{ $settings->phone }}</a>@endif
            @if($settings?->email)<a href="mailto:{{ $settings->email }}">{{ $settings->email }}</a>@endif
            @if($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>@endif
            @if($settings?->city)<span>{{ $settings->city }}{{ $settings->state ? ' / '.$settings->state : '' }}</span>@endif
        </div></div>
    </div>
    <div class="store-container wh-footer-bottom"><span>© {{ date('Y') }} {{ $storeName }}. Todos os direitos reservados.</span>@if($settings?->cnpj)<span>CNPJ {{ $settings->cnpj }}</span>@endif</div>
</footer>
