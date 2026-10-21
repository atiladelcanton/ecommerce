# SABA — Integração Asaas (21/10/2026)

Este projeto passa a usar **Asaas Checkout hospedado** como único gateway real disponível para novas compras. **Mercado Pago permanece no código e em registros financeiros históricos, mas NÃO é oferecido ao comprador nem selecionado automaticamente**.

## Antes de atualizar sua loja

- Faça backup do banco, código e `.env`. Não substitua o `APP_KEY` e não execute `migrate:fresh`.
- A versão anterior do Mercado Pago precisa estar instalada com a migration `2026_10_20_000001_create_real_payment_infrastructure.php` aplicada. A nova migration incremental `2026_10_21_000001_enable_asaas_and_archive_mercado_pago.php` desativa a integração Mercado Pago para novas vendas e adiciona apenas o campo JSON `payment_events.payload`.
- Pedidos e tentativas antigas do Mercado Pago continuam registrados; os webhooks históricos continuam processáveis e seu reconciliador permanece disponível.

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail artisan test --filter=AsaasCheckoutTest
./vendor/bin/sail artisan test
./vendor/bin/sail pint --test
./vendor/bin/sail npm run build
```

## Criar e configurar a conta

1. Abra o [Sandbox Asaas](https://sandbox.asaas.com/) e crie uma conta separada da conta de produção. Gere a **API Key Sandbox**; ela começa com `$aact_hmlg_`. Não remova o caractere `$`.
2. No Filament, vá a **Configurações → Meios de Pagamento → Asaas**. Selecione `sandbox`, informe a API Key, habilite Pix e/ou cartão, escolha o parcelamento (1–21x) e a validade (10–1440 minutos; padrão 60).
3. Crie um segredo aleatório com **pelo menos 32 caracteres** e use o mesmo valor na configuração de webhook Asaas (`authToken`) e no campo **Token de autenticação dos Webhooks** do SABA. Use tokens diferentes para sandbox e produção.
4. No Asaas, configure o webhook para a URL:

   `https://SEU-DOMINIO/webhooks/payments/asaas`

   Escolha os eventos `CHECKOUT_CREATED`, `CHECKOUT_PAID`, `CHECKOUT_CANCELED`, `CHECKOUT_EXPIRED`. O SABA valida o header `asaas-access-token` e armazena o ID do evento para deduplicação.
5. Salve e teste a conexão; só então ative Asaas no checkout. `APP_URL` deve ser o endereço **HTTPS público** da loja ou de um túnel HTTPS estável no sandbox.
6. Execute em terminais/serviços separados: `./vendor/bin/sail artisan queue:work --queue=default --tries=8` e `./vendor/bin/sail artisan schedule:work`. Em produção use worker supervisionado e cron do Laravel a cada minuto.

As credenciais são armazenadas criptografadas no banco e **não** precisam estar no `.env`. Preserve `QUEUE_CONNECTION=database` e um `APP_KEY` estável; não reutilize a chave de produção no sandbox.

## Regras de pagamento e estoque

- O SABA cria e congela os dados do pedido e reserva estoque antes de chamar o Asaas. Se a API retornar o ID do Checkout, o cliente segue para `https://asaas.com/checkoutSession/show?id=...`.
- O retorno do navegador **não aprova** o pedido. Apenas `CHECKOUT_PAID` autenticado, ligado ao checkout local, com valores conferidos em centavos contra o pedido, aprova e baixa estoque uma só vez.
- `CHECKOUT_CANCELED` ou `CHECKOUT_EXPIRED`, com token válido e checkout identificado, libera a reserva. Sem resposta conclusiva ou sem webhook, o estoque **continua reservado**, mesmo quando o prazo local é ultrapassado. Essa regra protege contra liberação prematura, mas exige operação de webhooks/filas sem falhas.
- A tela administrativa mostra cobranças pendentes e situações que exigem revisão. O botão **Solicitar cancelamento** envia a solicitação ao Asaas, porém **não libera o estoque até receber a confirmação por webhook**.
- Como a documentação do Checkout Asaas não oferece nesta integração um endpoint de consulta de estado equivalente ao `GET /v1/orders/{id}` do Mercado Pago nem uma garantia de criação idempotente no POST, um timeout na criação NÃO é repetido automaticamente. O checkout fica pendente e sinalizado para conferência manual. O reconciliador sinaliza falta de webhook, mas não inventa uma expiração.
- Pagamento recebido depois de expiração/liberação, divergência de valores ou cobrança duplicada exige intervenção humana. Não liberar envios automaticamente sem estoque.

## Limites do escopo

- Esta integração usa o Checkout Asaas (`POST /v3/checkouts`), com **Pix e cartão de crédito**, pagamento avulso ou parcelado. O Checkout Asaas não oferece boleto nessa modalidade; não foi criada integração de boleto/link de pagamento.
- Reembolsos financeiros e chargebacks ainda exigem conferência e operação no Asaas; nenhum reembolso é lançado automaticamente pelo SABA. Homologue esses processos antes de produção.
- A execução da suíte Laravel, que depende de `vendor/`, e testes com conta Asaas real/sandbox **não foram possíveis neste ambiente de empacotamento**. Os testes automatizados incluídos usam respostas `Http::fake()` e webhook simulado. Execução real requer credenciais e worker configurado.

## Referências oficiais

- https://docs.asaas.com/docs/introdu%C3%A7%C3%A3o-1
- https://docs.asaas.com/reference/criar-novo-checkout
- https://docs.asaas.com/docs/eventos-para-checkout
- https://docs.asaas.com/docs/authentication-2
- https://docs.asaas.com/docs/sandbox
- https://docs.asaas.com/reference/cancelar-um-checkout
