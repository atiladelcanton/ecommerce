@extends('influencers.layout')
@section('title', 'Entrar')
@section('content')
<section class="auth-card card">
    <p class="eyebrow">Acesso reservado</p>
    <h1>Seu painel de influenciador</h1>
    <p class="muted">Entre para consultar seus cupons, vendas atribuídas e comissões.</p>
    <form method="POST" action="{{ route('influencers.login.post') }}" class="form-grid">@csrf
        <label>E-mail<input type="email" name="email" value="{{ old('email') }}" required autocomplete="username"></label>
        <label>Senha<input type="password" name="password" required autocomplete="current-password"></label>
        <button type="submit" class="btn btn-primary">Entrar no painel →</button>
    </form>
    <p class="footnote">Seu acesso é fornecido pela administração da loja. Não use o login de cliente.</p>
</section>
@endsection
