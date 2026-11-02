<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Painel de influenciadores') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/influencers.css') }}">
</head>
<body>
    <header class="topbar">
        <div class="wrap top-inner">
            <a class="brand" href="{{ route('influencers.dashboard') }}"><span class="brandmark">✦</span><span><strong>Influencers</strong><small>Seu espaço de resultados</small></span></a>
            @auth('influencer')
                <span class="account">{{ auth('influencer')->user()->name }}</span>
                <form method="POST" action="{{ route('influencers.logout') }}">@csrf<button class="btn btn-light">Sair</button></form>
            @endauth
        </div>
        @auth('influencer')
            <nav class="wrap tabs" aria-label="Navegação do portal">
                <a class="{{ request()->routeIs('influencers.dashboard') ? 'selected' : '' }}" href="{{ route('influencers.dashboard') }}">Visão geral</a>
                <a class="{{ request()->routeIs('influencers.orders') ? 'selected' : '' }}" href="{{ route('influencers.orders') }}">Pedidos</a>
                <a class="{{ request()->routeIs('influencers.commissions') ? 'selected' : '' }}" href="{{ route('influencers.commissions') }}">Comissões</a>
                <a class="{{ request()->routeIs('influencers.payments') ? 'selected' : '' }}" href="{{ route('influencers.payments') }}">Pagamentos</a>
                <a class="{{ request()->routeIs('influencers.profile') ? 'selected' : '' }}" href="{{ route('influencers.profile') }}">Meu perfil</a>
            </nav>
        @endauth
    </header>
    <main class="wrap content">
        @if (session('success')) <div class="notice" role="status">{{ session('success') }}</div> @endif
        @if ($errors->any()) <div class="notice error" role="alert">{{ $errors->first() }}</div> @endif
        @yield('content')
    </main>
    <footer class="wrap footer">Os resultados são informativos. Comissões são devidas conforme aprovação e condições acordadas com a loja.</footer>
</body>
</html>
