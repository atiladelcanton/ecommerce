<x-store.layout :title="$address ? 'Editar endereço' : 'Novo endereço'">
    <div class="store-container store-account-page">
        <div class="store-account-heading"><div><p class="store-eyebrow">Área do cliente</p><h1 class="store-title mt-2">{{ $address ? 'Editar endereço' : 'Novo endereço' }}</h1></div><a class="store-button-secondary" href="{{ route('customer.addresses') }}">Voltar</a></div>
        <x-store.customer-nav />
        <form method="POST" action="{{ $address ? route('customer.addresses.update', $address) : route('customer.addresses.store') }}" class="store-account-card mt-6" x-data="{ loading: false, message: '', async lookup() { const cep = String(this.$refs.cep.value).replace(/\D/g,''); if (cep.length !== 8) return; this.loading = true; this.message = ''; try { const r = await fetch('{{ url('/minha-conta/consulta-cep') }}/' + cep, {headers:{'Accept':'application/json'}}); const d = await r.json(); if (!r.ok) { this.message = d.message || 'CEP não encontrado.'; return; } for (const key of ['street','neighborhood','city','state']) if (d[key]) this.$refs[key].value = d[key]; if (d.complement && !this.$refs.complement.value) this.$refs.complement.value = d.complement; } catch(e) { this.message = 'Não foi possível consultar o CEP agora.'; } finally { this.loading = false; } } }">
            @csrf @if($address) @method('PUT') @endif
            <fieldset><legend class="text-lg font-semibold">Informações do endereço</legend>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div><label class="store-label" for="label">Identificação</label><input class="store-input" id="label" name="label" value="{{ old('label', $address?->label) }}" placeholder="Casa, Empresa, Outro"></div>
                    <div><label class="store-label" for="postal_code">CEP</label><input x-ref="cep" @blur="lookup" data-saba-mask="postalCode" class="store-input" id="postal_code" name="postal_code" inputmode="numeric" maxlength="9" value="{{ \App\Support\BrazilianData::postalCode(old('postal_code', $address?->postal_code)) }}" required><p class="mt-1 text-xs text-store-muted" x-show="loading">Buscando endereço...</p><p class="mt-1 text-xs text-store-red" x-text="message"></p>@error('postal_code')<p class="mt-1 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                    <div class="sm:col-span-2"><label class="store-label" for="street">Rua</label><input x-ref="street" class="store-input" id="street" name="street" value="{{ old('street', $address?->street) }}" required></div>
                    <div><label class="store-label" for="number">Número</label><input class="store-input" id="number" name="number" value="{{ old('number', $address?->number) }}" required></div>
                    <div><label class="store-label" for="complement">Complemento</label><input x-ref="complement" class="store-input" id="complement" name="complement" value="{{ old('complement', $address?->complement) }}"></div>
                    <div><label class="store-label" for="neighborhood">Bairro</label><input x-ref="neighborhood" class="store-input" id="neighborhood" name="neighborhood" value="{{ old('neighborhood', $address?->neighborhood) }}" required></div>
                    <div><label class="store-label" for="city">Cidade</label><input x-ref="city" class="store-input" id="city" name="city" value="{{ old('city', $address?->city) }}" required></div>
                    <div><label class="store-label" for="state">Estado</label><select x-ref="state" class="store-input" id="state" name="state" required><option value="">Selecione</option>@foreach($states as $state)<option value="{{ $state }}" @selected(old('state', $address?->state) === $state)>{{ $state }}</option>@endforeach</select></div>
                    <label class="flex items-center gap-2 self-end pb-3 text-sm"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address?->is_default ?? false)) class="accent-store-red"> Usar como endereço principal</label>
                </div>
            </fieldset>
            <div class="mt-6 flex gap-3"><button class="store-button" type="submit">Salvar endereço</button><a class="store-button-secondary" href="{{ route('customer.addresses') }}">Cancelar</a></div>
        </form>
    </div>
</x-store.layout>
