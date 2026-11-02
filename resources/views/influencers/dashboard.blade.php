@extends('influencers.layout')
@section('title', 'Visão geral')
@section('content')
<div class="intro"><p class="eyebrow">Bem-vindo de volta</p><h1>Olá, {{ $influencer->name }}!</h1><p class="muted">Acompanhe a evolução das suas indicações em tempo real.</p></div>
<section class="stats" aria-label="Indicadores">
    <div class="stat"><span>Pedidos atribuídos</span><strong>{{ $stats['order_count'] }}</strong><small>{{ $stats['paid_order_count'] }} com pagamento confirmado</small></div>
    <div class="stat"><span>Vendas confirmadas</span><strong>R$ {{ number_format($stats['confirmed_sales_cents']/100, 2, ',', '.') }}</strong><small>Subtotal de produtos, antes de frete</small></div>
    <div class="stat"><span>Comissões previstas</span><strong>R$ {{ number_format($stats['forecast_cents']/100, 2, ',', '.') }}</strong><small>Aguardando aprovação da loja</small></div>
    <div class="stat"><span>Comissões aprovadas</span><strong>R$ {{ number_format($stats['approved_cents']/100, 2, ',', '.') }}</strong><small>Aguardando transferência</small></div>
    <div class="stat"><span>Total pago</span><strong>R$ {{ number_format($stats['paid_cents']/100, 2, ',', '.') }}</strong><small>Repasses registrados</small></div>
</section>
<div class="grid-two">
<section class="card"><div class="card-title"><div><p class="eyebrow">Compartilhe e converta</p><h2>Meus cupons</h2></div><span class="counter">{{ $coupons->count() }}</span></div>
    @forelse ($coupons as $coupon)
    <div class="couponline"><div><strong class="coupon-code">{{ $coupon->code }}</strong><p class="muted">{{ $coupon->discount_type === 'percentage' ? $coupon->value.'% de desconto' : 'Desconto de R$ '.number_format($coupon->value /100,2,',','.') }} · {{ $coupon->uses_count }} compra(s) confirmada(s)</p></div><span class="badge {{ $coupon->is_active ? 'success' : '' }}">{{ $coupon->is_active ? 'Ativo' : 'Inativo' }}</span></div>
    @empty <p class="empty">Ainda não há cupons vinculados ao seu perfil. Solicite ao administrador.</p> @endforelse
    <p class="footnote">Uso do cupom respeita as regras e restrições definidas pela loja. Reservas pendentes não contam como compra confirmada.</p>
</section>
<section class="card"><div class="card-title"><div><p class="eyebrow">Movimentações</p><h2>Últimas comissões</h2></div><a href="{{ route('influencers.commissions') }}" class="subtle-link">Ver todas →</a></div>
    @forelse($recentCommissions as $commission)
        <div class="couponline"><div><strong>{{ $commission->order?->number ?? 'Pedido' }}</strong><p class="muted">{{ \App\Domain\Influencer\Models\InfluencerCommission::statuses()[$commission->status] ?? $commission->status }}</p></div><strong>R$ {{ number_format($commission->amount_cents/100,2,',','.') }}</strong></div>
    @empty <p class="empty">Suas comissões aparecerão aqui quando clientes utilizarem seu cupom.</p> @endforelse
</section>
</div>
<div class="info-strip"><strong>Como funciona:</strong> o pedido gera uma previsão. Depois que o pagamento é confirmado, a comissão entra em análise e poderá ser aprovada. O pagamento ao influenciador é registrado pela loja após realizar o repasse.</div>
@endsection
