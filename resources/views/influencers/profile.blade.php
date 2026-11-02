@extends('influencers.layout')
@section('title', 'Meu perfil')
@section('content')
<div class="intro"><p class="eyebrow">Minha conta</p><h1>Meu perfil</h1><p class="muted">Atualize suas redes sociais, chave Pix e senha. Dados de comissão e nome são definidos pela loja.</p></div>
<section class="card profile-card"><p><strong>{{ $influencer->name }}</strong> · {{ $influencer->email }}</p><form class="form-grid" method="POST" action="{{ route('influencers.profile.update') }}">@csrf @method('PUT')
<label>Instagram<input name="instagram" value="{{ old('instagram', $influencer->instagram) }}" placeholder="@seudominio"></label>
<label>TikTok<input name="tiktok" value="{{ old('tiktok', $influencer->tiktok) }}" placeholder="@seudominio"></label>
<label>Chave Pix (opcional)<input type="text" name="pix_key" autocomplete="off" placeholder="Deixe em branco para manter a atual"></label>
<label>Nova senha<input type="password" name="password" autocomplete="new-password" placeholder="Deixe em branco para manter"></label>
<label>Confirme a nova senha<input type="password" name="password_confirmation" autocomplete="new-password"></label>
<button class="btn btn-primary">Salvar alterações</button></form></section>
@endsection
