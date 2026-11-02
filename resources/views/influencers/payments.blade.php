@extends('influencers.layout')
@section('title', 'Histórico de pagamentos')
@section('content')
<div class="intro"><p class="eyebrow">Financeiro</p><h1>Histórico de pagamentos</h1><p class="muted">Repasses que a loja marcou como pagos após realizar a transferência. Consulte sua instituição financeira para confirmar o recebimento.</p></div>
<div class="card table-card"><div class="table-scroll"><table><thead><tr><th>Pedido de origem</th><th>Data do repasse</th><th>Comissão paga</th><th>Situação</th></tr></thead><tbody>
@forelse($payments as $payment)
<tr><td>{{ $payment->order?->number ?? '—' }}</td><td>{{ $payment->paid_at?->format('d/m/Y H:i') ?? '—' }}</td><td><strong>R$ {{ number_format($payment->amount_cents / 100, 2, ',', '.') }}</strong></td><td><span class="badge success">Registrado como pago</span></td></tr>
@empty <tr><td colspan="4" class="empty">Ainda não há pagamentos registrados para sua conta.</td></tr> @endforelse
</tbody></table></div><div class="pagination">{{ $payments->links() }}</div></div>
<p class="footnote">O portal registra a informação fornecida pelo administrador. Ele não emite comprovantes bancários e não realiza transferências Pix.</p>
@endsection
