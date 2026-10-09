<x-store.layout title="Contato" description="Consulte os canais de contato da SABA." :canonical="route('store.contact')">
    <div class="store-container store-institutional-page">
        <x-store.breadcrumb :items="[['label' => 'Contato']]" />
        <section class="store-institutional-intro">
            <p class="store-eyebrow">Fale com a SABA</p>
            <h1>Contato</h1>
            <p>Converse com nossa equipe sobre equipamentos e dúvidas sobre o catálogo.</p>
        </section>
        {{-- Contact channels only. No form, persistence, email submission or simulated success in this slice. --}}
        <div class="store-contact-grid">
            @if($phoneUrl)
                <section class="store-institutional-card">
                    <x-heroicon-o-phone class="store-contact-icon size-6" />
                    <h2>Telefone</h2>
                    <a class="store-contact-value" href="{{ $phoneUrl }}">{{ $settings->phone }}</a>
                </section>
            @endif
            @if($whatsappUrl)
                <section class="store-institutional-card">
                    <x-heroicon-o-chat-bubble-oval-left-ellipsis class="store-contact-icon size-6" />
                    <h2>WhatsApp</h2>
                    <p class="store-contact-value">{{ $settings->whatsapp }}</p>
                    <a class="store-button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Falar pelo WhatsApp <x-heroicon-o-arrow-up-right class="size-4" /></a>
                </section>
            @endif
            @if($settings?->email)
                <section class="store-institutional-card">
                    <x-heroicon-o-envelope class="store-contact-icon size-6" />
                    <h2>E-mail</h2>
                    <a class="store-contact-value" href="mailto:{{ $settings->email }}">{{ $settings->email }}</a>
                </section>
            @endif
            @if($addressLines)
                <section class="store-institutional-card">
                    <x-heroicon-o-map-pin class="store-contact-icon size-6" />
                    <h2>Endereço</h2>
                    <address class="store-contact-address">
                        @foreach($addressLines as $line)<span>{{ $line }}</span>@endforeach
                    </address>
                </section>
            @endif
        </div>
        @unless($hasContactChannels)
            <p class="store-contact-empty">Os canais de contato serão disponibilizados em breve.</p>
        @endunless
    </div>
</x-store.layout>
