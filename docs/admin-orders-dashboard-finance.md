# Admin: Pedidos, Dashboard e Financeiro

## Pedidos

- `Pedidos Kanban`: visão operacional com busca/filtros e atualização a cada 20s.
- `Pedidos`: listagem completa com filtros e página detalhada.
- Pagamento aprovado move `Pedido recebido` para `Pedido pago` pela Action de pagamento.
- Envios normais pedem transportadora + rastreio.
- Retirada usa `Pronto para retirada`.

## Dashboard

Widgets nativos do Filament:

- Total de pedidos, movimento do mês, faturamento pago, ticket médio e pedidos que exigem ação.
- Linha de pedidos nos últimos 12 meses.
- Distribuição por status.
- Pedidos pagos/em preparação que exigem atenção.

Os charts usam `ChartWidget` nativo para evitar uma nova dependência obrigatória. O `jccoca/filament-chart-palette` pode ser conectado posteriormente apenas para abstrair a paleta, sem alterar queries ou widgets.

## Financeiro

`Fluxo de Caixa` registra:

- Entradas automáticas de pagamentos aprovados.
- Entradas manuais.
- Saídas manuais.
- Histórico mensal de entradas e saídas.
- Saldo do mês e acumulado.

O módulo não implementa contas a pagar/receber, conciliação bancária ou emissão fiscal: permanece um controle de caixa enxuto.
