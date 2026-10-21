# Hotfix SABA — Mercado Pago HTTP 400 (unsupported_properties)

Diagnóstico confirmado no log do pedido SABA-000012: `POST /v1/orders` recebeu HTTP 400 com `unsupported_properties`. A resposta não detalhou o caminho da propriedade rejeitada, mas o payload enviado contém dois campos não documentados para os itens de Checkout Pro: `items[].unit_measure` e `items[].total_amount`.

## Correção

- Removidos `items[].unit_measure` e `items[].total_amount` de `MercadoPagoGateway`.
- Os itens são enviados apenas com `title`, `quantity` e `unit_price` (decimal em reais como string). `total_amount` permanece na **raiz** da order e é validado em centavos.
- Teste de regressão atualizado para exigir exatamente o conjunto suportado de propriedades dos itens.
- `docs/payments.md` corrigido para não recomendar propriedades indevidas.
- Não altera credenciais, filas, webhooks, migrations ou política de reserva de estoque.

## Instalação

Faça backup dos arquivos alterados. Extraia este ZIP na raiz do projeto, preservando a estrutura de diretórios. Depois execute:

```bash
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail artisan test --filter=MercadoPagoCheckoutTest
./vendor/bin/sail pint --test
```

Reinicie o worker de filas se ele já estava rodando (`./vendor/bin/sail artisan queue:restart`). Refaça a compra no sandbox. Verifique `storage/logs/laravel.log` caso a API ainda retorne erro. **Não envie tokens, segredo do webhook nem payload com dados do comprador.**

## Observações

- A rejeição HTTP 400 indica campo não suportado, mas a API não identificou o caminho exato: a correção precisa ser homologada em uma nova tentativa.
- O pedido SABA-000012, se permanece com pagamento de tentativa falha, poderá precisar de nova tentativa de pagamento pelo fluxo normal do SABA.
- Este ambiente não contém `vendor/` nem `node_modules`. Validamos sintaxe PHP e arquivos ZIP; a suíte PHPUnit precisa ser executada na instalação.

Documentação oficial: https://www.mercadopago.com.br/developers/pt/docs/checkout-pro-orders/create-order
