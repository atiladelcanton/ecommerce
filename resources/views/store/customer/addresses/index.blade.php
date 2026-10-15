<x-store.layout title="Meus endereços">
    <div class="store-container store-account-page">
        <div class="store-account-heading">
            <div><p class="store-eyebrow">Área do cliente</p><h1 class="store-title mt-2">Meus endereços</h1><p class="mt-2 text-sm text-store-muted">Gerencie os endereços usados no checkout.</p></div>
            <a class="store-button" href="{{ route('customer.addresses.create') }}">+ Adicionar endereço</a>
        </div>
        <x-store.customer-nav />
        <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($addresses as $address)
                <article class="store-account-card flex min-h-52 flex-col">
                    <div class="flex items-start justify-between gap-3">
                        <div><h2 class="font-semibold">{{ $address->label ?: 'Endereço' }}</h2><p class="mt-2 text-sm leading-6 text-store-muted">{{ $address->formattedLine() }}<br>{{ $address->neighborhood }} · {{ $address->city }}/{{ $address->state }}<br>CEP {{ \App\Support\BrazilianData::postalCode($address->postal_code) }}</p></div>
                        @if($address->is_default)<span class="rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-store-red">Principal</span>@endif
                    </div>
                    <div class="mt-auto flex flex-wrap gap-3 pt-5">
                        <a class="store-text-link" href="{{ route('customer.addresses.edit', $address) }}">Editar</a>
                        @unless($address->is_default)
                            <form method="POST" action="{{ route('customer.addresses.default', $address) }}">@csrf<button class="store-text-link" type="submit">Tornar principal</button></form>
                        @endunless
                        <form method="POST" action="{{ route('customer.addresses.destroy', $address) }}" onsubmit="return confirm('Remover este endereço?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-store-red hover:underline" type="submit">Excluir</button></form>
                    </div>
                </article>
            @empty
                <div class="store-account-card md:col-span-2 xl:col-span-3 text-center"><p class="text-store-muted">Você ainda não possui endereços cadastrados.</p><a class="store-button mt-5" href="{{ route('customer.addresses.create') }}">Cadastrar primeiro endereço</a></div>
            @endforelse
        </div>
    </div>
</x-store.layout>
