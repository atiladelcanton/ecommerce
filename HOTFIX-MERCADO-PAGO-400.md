# Correção: Mercado Pago HTTP 400 — Checkout SABA

Data: 21/10/2026

## Alterações

- No checkout público, substitui a mensagem `O Mercado Pago recusou ... (HTTP 400)` por texto comercial neutro.
- Adiciona log estruturado e sanitizado na criação de order (`error_code`, `error_message`, `details`, `request_id`, `http_status`, `order_number`), sem tokens nem payload integral.
- Diagnostica erros de rede e respostas 2xx incompletas sem vazar os detalhes ao cliente.
- Normaliza a duração ISO-8601 `P3D` para 72 h e inclui `unit_measure` e `total_amount` nos itens, seguindo o exemplo oficial da Orders API.
- Evita `config.payment_method: []` quando os métodos offline estão habilitados.
- Em rejeições definitivas 4xx, preserva a rotina de liberação da reserva. Em erro incerto/rede/5xx, preserva reserva e idempotência.
- Acrescenta testes para a mensagem pública, sanitização do log e payload de pagamento offline.

## Arquivos modificados

- `app/Infrastructure/Payments/MercadoPagoGateway.php`
- `app/Actions/Order/PayOrderAction.php`
- `tests/Feature/Payment/MercadoPagoCheckoutTest.php`
- `docs/payments.md`

## Como aplicar

Faça backup e atualize o código do projeto. Este hotfix **não adiciona migrations**. Não sobrescreva o `.env` nem execute `migrate:fresh`.

```bash
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail artisan test --filter=MercadoPagoCheckoutTest
./vendor/bin/sail pint --test
```

Confirme no `.env` que o canal de log não descarta warnings:

```env
LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=debug
```

Refaça uma compra de teste. Verifique:

```bash
grep -n 'Payment provider rejected checkout initialization' storage/logs/laravel.log | tail -5
```

O erro antigo não possui informações suficientes para identificar a causa exata: a integração descartava o corpo de erro da API. Após aplicar o hotfix, `error_code`, `error_message` e `details` orientarão a próxima correção.

**Segurança:** antes de compartilhar qualquer log, revise e oculte identificadores e dados de clientes.

## Verificações deste pacote

`php -l` validado nos arquivos alterados. A suíte PHPUnit depende de `vendor/` (não incluso no ZIP), portanto deve ser executada no ambiente Sail da instalação. Não houve integração real com o Mercado Pago neste ambiente.
