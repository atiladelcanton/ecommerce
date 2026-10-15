<x-store.layout title="Entrar na minha conta">
    <div class="store-container store-account-auth">
        <section class="store-account-auth-card">
            <p class="store-eyebrow">Área do cliente</p>
            <h1 class="store-title mt-2">Acesse sua conta</h1>
            <p class="mt-3 text-sm leading-6 text-store-muted">Veja seus pedidos e acompanhe cada etapa da entrega.</p>
            <form method="POST" action="{{ route('customer.login.store') }}" class="mt-7 grid gap-4">
                @csrf
                <div><label class="store-label" for="email">E-mail</label><input class="store-input" id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>@error('email')<p class="mt-2 text-sm text-store-red">{{ $message }}</p>@enderror</div>
                <div><label class="store-label" for="password">Senha</label><input class="store-input" id="password" name="password" type="password" autocomplete="current-password" required></div>
                <label class="flex items-center gap-2 text-sm text-store-muted"><input type="checkbox" name="remember" value="1"> Lembrar de mim</label>
                <button class="store-button w-full" type="submit">Entrar</button>
            </form>
            <p class="mt-6 text-center text-sm text-store-muted">Ainda não tem conta? <a class="store-text-link" href="{{ route('customer.register', ['email' => old('email', $email)]) }}">Criar conta</a></p>
        </section>
    </div>
</x-store.layout>
