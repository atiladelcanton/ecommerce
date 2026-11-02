@extends('influencers.layout')
@section('title', 'Pedidos indicados')
@section('content')
<div class="intro"><p class="eyebrow">Acompanhamento</p><h1>Pedidos indicados</h1><p class="muted">Lista de compras atribuídas aos seus cupons. Dados pessoais dos compradores não são exibidos.</p></div>
<div class="card table-card"><div class="table-scroll"><table><thead><tr><th>Pedido</th><th>Data</th><th>Cupom</th><th>Situação</th><th>Subtotal</th></tr></thead><tbody>
@forelse($orders as $order)
<tr><td><strong>{{ $order->number }}</strong></td><td>{{ $order->created_at?->format('d/m/Y') }}</td><td>{{ $order->coupon?->code }}</td><td><span class="badge">{{ $order->status?->label() ?? '—' }}</span></td><td>R$ {{ number_format($order->subtotal_cents/100,2,',','.') }}</td></tr>
@empty <tr><td colspan="5" class="empty">Nenhum pedido atribuído ainda.</td></tr> @endforelse
</tbody></table></div><div class="pagination">{{ $orders->links() }}</div></div>
@endsection
