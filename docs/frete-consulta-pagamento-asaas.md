# Fluxo de frete sob consulta até o pagamento Asaas

## Máquina de estados

1. `orders.status=pending`, `shipping_pricing_status=awaiting_quote`, `shipping_cents=NULL`, `total_cents=NULL`: pedido sem cobrança; reserva de estoque de até 24 horas.
2. `PublishOrderShippingQuoteAction`: gera revisão de `order_shipping_quotes` (`quoted`), snapshot de frete manual, `shipping_cents` e `total_cents`, sem alterar itens.
3. Link de `GetOrderShippingQuoteLinkAction`: URL temporária assinada; proprietário deve se autenticar, visitante usa link secreto. GET não aceita proposta.
4. `ConfirmQuotedOrderAction`: valida revisão/validade, revalida produtos/estoque, reserva novamente se necessário, grava `confirmed`/`accepted_at` e só depois inicia pagamento remoto. Clique repetido não cria outra cobrança.
5. `PayOrderAction`: valida finalização, mantém stock `payment_hold`, reserva cupom quando aplicável; cria placeholder `initiating-*` antes do POST Asaas; não repete POST em resultado incerto. `PaymentRequest` leva único item de total líquido (incluindo desconto e frete) para garantir igualdade com snapshot persistido.
6. `CHECKOUT_PAID`: webhook autenticado do Asaas verifica a referência e o valor; consome estoque/cupom, grava pagamento e transita para `paid`. `CHECKOUT_CANCELED` / `CHECKOUT_EXPIRED` liberam hold apenas com confirmação do provedor.
7. Depois de `paid`, fluxo operacional de preparação e entrega manual, sem etiqueta Melhor Envio para frete negociado externamente.

## Segurança

- Nunca gerar um segundo pedido para nova cotação ou retry.
- Nunca confirmar pagamento pela navegação do usuário.
- Não alterar frete após `confirmed` nem com checkout Asaas pendente.
- Não cancelar pedido com cobrança remota pendente; encerrar primeiro no Asaas.
- Não liberar `payment_hold` por vencimento local; apenas com evento terminal autorizado.
- Não repetir `POST /checkouts` depois de timeout/5xx/resposta incerta; investigar manualmente.
- Retorno de pagamento exige HMAC por pedido e acesso do comprador a tela de acompanhamento exige sessão privada ou conta autenticada.

## Envio de cotação

`SendOrderShippingQuoteEmailAction` é acionado explicitamente no detalhe do pedido; usa mailer configurado na instalação. O WhatsApp abre uma mensagem, sem envio automático.

## Implantação e validação

Sem novas migrations neste pacote, mas requer a migration de pagamentos de 20/10/2026 já aplicada, incluindo `inventory_reservations.payment_hold`. Necessário `APP_URL=https://...`, webhook Asaas acessível via HTTPS, autenticação por `asaas-access-token`, cron Laravel ativo para conciliação e `php artisan queue:work` ativo quando `QUEUE_CONNECTION=database`; webhooks são processados por jobs e não exigem Redis. Nunca use Sandbox para cobranças reais. O driver `MAIL_MAILER=log` não envia e-mail.

Testar: 1) solicitar cotação; 2) publicar/revisar; 3) cliente aceitar; 4) abrir Asaas Sandbox; 5) duplo clique; 6) webhook pago; 7) cancelar/expirar pelo provedor; 8) reabrir pedido na área do cliente; 9) verificar itens/estoque/cupom e evitar checkout duplicado.
