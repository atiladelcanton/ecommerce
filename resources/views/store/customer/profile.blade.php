<x-store.layout title="Meus dados">
    <div class="store-container store-account-page">
        <div class="store-account-heading"><div><p class="store-eyebrow">Área do cliente</p><h1 class="store-title mt-2">Meus dados</h1><p class="mt-2 text-sm text-store-muted">Atualize seus dados de contato e sua senha.</p></div></div>
        <x-store.customer-nav />
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('customer.profile.update') }}" class="store-account-card">@csrf @method('PUT')
                <fieldset><legend class="text-lg font-semibold">Dados pessoais</legend><div class="mt-5 grid gap-4">
                    <div><label class="store-label" for="name">Nome</label><input class="store-input" id="name" name="name" value="{{ old('name', $customer->name) }}" required>@error('name')<p class="mt-1 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                    <div><label class="store-label" for="email">E-mail</label><input class="store-input" id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" required>@error('email')<p class="mt-1 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                    <div><label class="store-label" for="phone">Telefone</label><input data-saba-mask="phone" class="store-input" id="phone" name="phone" value="{{ \App\Support\BrazilianData::phone(old('phone', $customer->phone)) }}" required>@error('phone')<p class="mt-1 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                    <div><label class="store-label">CPF/CNPJ</label><input class="store-input bg-slate-50 text-slate-500" value="{{ \App\Support\BrazilianData::document($customer->document) }}" readonly><p class="mt-2 text-xs text-store-muted">Para corrigir seu CPF/CNPJ, entre em contato com a SABA.</p></div>
                </div></fieldset>
                <button class="store-button mt-6" type="submit">Salvar dados</button>
            </form>
            <form method="POST" action="{{ route('customer.password.update') }}" class="store-account-card">@csrf @method('PUT')
                <fieldset><legend class="text-lg font-semibold">Alterar senha</legend><div class="mt-5 grid gap-4">
                    <div><label class="store-label" for="current_password">Senha atual</label><input class="store-input" id="current_password" name="current_password" type="password" autocomplete="current-password" required>@error('current_password')<p class="mt-1 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                    <div><label class="store-label" for="password">Nova senha</label><input class="store-input" id="password" name="password" type="password" autocomplete="new-password" required>@error('password')<p class="mt-1 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                    <div><label class="store-label" for="password_confirmation">Confirmar nova senha</label><input class="store-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
                </div></fieldset>
                <button class="store-button mt-6" type="submit">Alterar senha</button>
            </form>
        </div>
    </div>
</x-store.layout>
