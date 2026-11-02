@extends('influencers.layout')
@section('title', 'Comissões')
@section('content')
<div class="intro"><p class="eyebrow">Seu financeiro</p><h1>Minhas comissões</h1><p class="muted">Valor calculado sobre os produtos pagos após descontos, sem o frete. Transferências são feitas pela loja fora do portal.</p></div>
<div class="card table-card"><div class="table-scroll"><table><thead><tr><th>Pedido</th><th>Data</th><th>Base</th><th>Percentual</th><th>Comissão</th><th>Status</th></tr></thead><tbody>
@forelse($commissions as $commission)
<tr><td><strong>{{ $commission->order?->number ?? '—' }}</strong></td><td>{{ $commission->created_at?->format('d/m/Y') }}</td><td>R$ {{ number_format($commission->base_cents/100,2,',','.') }}</td><td>{{ number_format($commission->commission_bps/100,2,',','.') }}%</td><td><strong>R$ {{ number_format($commission->amount_cents/100,2,',','.') }}</strong></td><td><span class="badge {{ $commission->status === 'paid' ? 'success' : '' }}">{{ \App\Domain\Influencer\Models\InfluencerCommission::statuses()[$commission->status] ?? $commission->status }}</span></td></tr>
@empty <tr><td colspan="6" class="empty">Nenhuma comissão registrada.</td></tr> @endforelse
</tbody></table></div><div class="pagination">{{ $commissions->links() }}</div></div>
@endsection
