# Admin: Pedidos e Dashboard Comercial

## Pedidos

- `Pedidos Kanban`: visão operacional com busca/filtros e atualização periódica.
- `Pedidos`: listagem completa com filtros e página detalhada.
- Pagamento aprovado move `Pedido recebido` para `Pedido pago` pela Action de pagamento.
- Envios normais pedem transportadora + rastreio.
- Retirada usa `Pronto para retirada`.

## Dashboard

O dashboard é comercial e operacional, não um ERP financeiro.

Widgets:

- Total de pedidos, faturamento pago, ticket médio e pedidos que exigem ação.
- Evolução de pedidos nos últimos 12 meses.
- Distribuição por status.
- Comparativo mensal de pedidos entregues x cancelados nos últimos 12 meses, usando `delivered_at` e `cancelled_at` em vez da data de criação do pedido.
- Resumo mensal de desfechos com entregues, cancelados, taxa de cancelamento e valor dos pedidos cancelados. A taxa considera apenas pedidos com desfecho (`cancelados / (entregues + cancelados)`).
- Pedidos pagos/em preparação que exigem atenção.
- Produtos mais vendidos no mês considerando somente pedidos com pagamento aprovado.

Os charts usam `ChartWidget` nativo para evitar dependência obrigatória adicional. O `jccoca/filament-chart-palette` pode ser conectado posteriormente apenas para abstrair a paleta visual.

## Financeiro fora do MVP

Não existe tela de `Fluxo de Caixa` no MVP. Um e-commerce conhece vendas e pagamentos, mas não conhece necessariamente folha, fornecedores, impostos, aluguel, energia, compras e demais despesas da empresa. Exibir `saldo` como se fosse caixa/lucro seria potencialmente enganoso.

`payments` continua sendo parte essencial do domínio para tentativas, aprovação, falha, estorno e idempotência. Indicadores comerciais derivam de Pedidos e Payments. Um módulo financeiro completo deve ser criado futuramente apenas mediante requisito explícito.
