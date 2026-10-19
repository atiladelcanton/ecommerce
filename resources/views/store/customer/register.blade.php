<x-store.layout title="Criar minha conta">
    <div class="store-container store-account-auth">
        <section class="store-account-auth-card">
            <p class="store-eyebrow">Área do cliente</p>
            <h1 class="store-title mt-2">Crie sua conta</h1>
            <p class="mt-3 text-sm leading-6 text-store-muted">Use o mesmo e-mail do pedido para vinculá-lo à sua conta quando ele tiver sido feito nesta sessão.</p>
            <form method="POST" action="{{ route('customer.register.store') }}" class="mt-7 grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2"><label class="store-label" for="name">Nome</label><input class="store-input" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required>@error('name')<p class="mt-2 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                <div><label class="store-label" for="email">E-mail</label><input class="store-input" id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>@error('email')<p class="mt-2 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                <div><label class="store-label" for="phone">Telefone</label><input data-saba-mask="phone" class="store-input" id="phone" name="phone" value="{{ \App\Support\BrazilianData::phone(old('phone')) }}" autocomplete="tel" maxlength="16" required>@error('phone')<p class="mt-2 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                <div><label class="store-label" for="document">CPF/CNPJ</label><input data-saba-mask="document" class="store-input" id="document" name="document" value="{{ \App\Support\BrazilianData::document(old('document')) }}" inputmode="numeric" maxlength="18" required>@error('document')<p class="mt-2 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                <div><label class="store-label" for="birth_date">Data de nascimento <span class="font-normal text-store-muted">(opcional)</span></label><input class="store-input" id="birth_date" name="birth_date" type="date" value="{{ old('birth_date') }}" max="{{ now()->toDateString() }}">@error('birth_date')<p class="mt-2 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                <div><label class="store-label" for="password">Senha</label><input class="store-input" id="password" name="password" type="password" autocomplete="new-password" required>@error('password')<p class="mt-2 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                <div><label class="store-label" for="password_confirmation">Confirmar senha</label><input class="store-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
                <button class="store-button sm:col-span-2" type="submit">Criar conta</button>
            </form>
            <p class="mt-6 text-center text-sm text-store-muted">Já tem conta? <a class="store-text-link" href="{{ route('customer.login', ['email' => old('email', $email)]) }}">Entrar</a></p>
        </section>
    </div>
</x-store.layout>
