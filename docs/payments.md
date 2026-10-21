# Meios de Pagamento — Asaas Checkout

Estado do repositório em 21/10/2026. Código implementado, **homologação de pagamento real pendente**. Leia `INSTALACAO-ASAAS.md` antes de implantar.

## Providers

- O `PaymentGatewayRegistry::active()` seleciona somente integrações `asaas` ativas para novas compras. `mercado_pago` mantém adapter, webhook e conciliação para **pagamentos históricos**, mas não pode ser selecionado pelo checkout público. A migration incremental desativa os registros antigos de Mercado Pago.
- Em ambientes local/testing, `FakePaymentGateway` permanece disponível apenas quando não há Asaas ativo e `PAYMENT_GATEWAY=fake`. Nunca há fallback fake em produção.
- `AsaasGateway` usa Laravel HTTP Client, `access_token`, `User-Agent`, URL sandbox/produção independente e `POST /v3/checkouts`. Exibe somente a URL oficial `https://asaas.com/checkoutSession/show?id=...`.
- Quando houver `customerData`, o `PayOrderAction` repassa o snapshot de `order_addresses` ao adapter Asaas. Os campos `address`, `addressNumber`, `postalCode` e `province` são preenchidos com logradouro, número, CEP normalizado e bairro; complemento é opcional. `city` exige código IBGE e não deve receber o nome da cidade armazenado no pedido. Erros de validação HTTP 400 registram somente códigos e campos, sem dados pessoais.
- `billingTypes`: PIX/CREDIT_CARD. `chargeTypes`: DETACHED e opcional INSTALLMENT. `minutesToExpire`: de 10 até 1440 minutos. Totais do domínio são centavos inteiros, convertidos com representação decimal exata para o JSON numérico do Asaas; não se utiliza float em cálculos financeiros.
- Credenciais criptografadas em `payment_integrations` por provider/ambiente. Configuração via `Filament/Pages/PaymentMethods` e `UpdateAsaasIntegrationAction`.

## Segurança e reservas

1. `PayOrderAction` cria um `Payment` PENDING com uma operação local, segura a reserva `payment_hold=true`, grava a intenção e chama o provider **fora de transação SQL**.
2. Timeout, HTTP 5xx, rate limit ou resposta inconclusiva deixam a cobrança e o estoque pendentes; NÃO é feito retry automático do POST Asaas, pois não há garantia oficial documentada de idempotência nessa criação. Caso remoto confirmado pelo webhook pode recuperar a intenção local.
3. Uma rejeição de validação definitivamente sem checkout libera reserva; nenhum segredo ou payload de cliente é exibido na loja ou registrado em log.
4. Webhook `POST /webhooks/payments/asaas` verifica o segredo via header `asaas-access-token` e `hash_equals`, armazena evento único com payload mínimo e enfileira processamento.
5. `ProcessAsaasCheckoutEventAction` verifica `checkout.id`, ambiente, `externalReference` quando presente, `items` e total exato em centavos; `CHECKOUT_PAID` aprova pagamento e consome a reserva uma só vez. Eventos duplicados ou atrasados não repetem a baixa nem regridem aprovado.
6. `CHECKOUT_EXPIRED` / `CHECKOUT_CANCELED` libera a reserva apenas quando autenticamente confirmado. Prazo local decorrido **não** libera estoque. O scheduler assinala a necessidade de revisão de cobranças sem evento terminal.
7. Operador pode solicitar cancelamento remoto da sessão com `CancelAsaasCheckoutAction`, sem liberar estoque até o webhook. Reembolsos e chargebacks são etapas ainda não integradas.

## Testes

`tests/Feature/Payment/AsaasCheckoutTest.php` cobre seleção do provider, criação com valor exato, reserva após 24h, assinatura, idempotência da aprovação, expiração e divergência de valor. Testes antigos de MP mantêm um **override somente em testing** para exercitar o adapter histórico, sem disponibilizá-lo ao comprador.

**Não executar `migrate:fresh` na loja existente.** Consulte `INSTALACAO-ASAAS.md` para implantação e configurações.
