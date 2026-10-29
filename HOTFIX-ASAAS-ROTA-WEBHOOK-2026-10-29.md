# Correção da tela Meios de Pagamento — rota webhook Asaas

## Problema

Ao abrir `/admin/payment-methods`, a página falhava com:

`Symfony\Component\Routing\Exception\RouteNotFoundException: Route [webhooks.payments.asaas] not defined.`

O Blade de Meios de Pagamento chama `route('webhooks.payments.asaas')`, mas a rota estava ausente em `routes/web.php`. O controlador de recebimento de eventos já existia em `app/Http/Controllers/Payment/AsaasWebhookController.php`.

## Alterações

- `routes/web.php`: registra `POST /webhooks/payments/asaas`, com o nome `webhooks.payments.asaas`, usando o controlador existente.
- `bootstrap/app.php`: inclui somente `webhooks/payments/asaas` na exceção de CSRF, porque chamadas servidor-a-servidor do Asaas não enviam o token de sessão do Laravel. O controlador existente **continua exigindo o cabeçalho secreto `asaas-access-token`**.
- `tests/Feature/Payment/AsaasWebhookRouteTest.php`: verifica a rota nomeada, o método HTTP e a rejeição de eventos sem autenticação.

## Instalação

1. Faça um backup dos arquivos anteriores.
2. Extraia o ZIP incremental **na raiz do seu projeto Laravel**, mantendo a estrutura de diretórios e substituindo os arquivos existentes.
3. Execute:

```bash
php artisan optimize:clear
php artisan route:list --name=webhooks.payments.asaas
```

A saída do segundo comando deve listar `POST webhooks/payments/asaas`.

4. Reabra `/admin/payment-methods`.
5. Em homologação, teste o recebimento de um evento real do Asaas e a atualização do pedido correspondente. **Não basta ver a tela abrir para confirmar o processamento de pagamentos.**

Não há migrations, alterações de banco, dependências adicionais ou necessidade de compilar o frontend.

## Observação sobre segurança

Não configure webhooks com URLs `localhost`: o Asaas precisa alcançar uma URL pública HTTPS. Mantenha o token secreto de webhook configurado igualmente no Asaas e no SABA.
