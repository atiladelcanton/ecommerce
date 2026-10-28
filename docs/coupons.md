# Cupons SABA — estrutura preservada (28/10/2026)

A tabela `coupons` e as tabelas `coupon_categories`, `coupon_products`, `coupon_usages` e `coupon_claims` foram criadas pela migration `2026_10_21_000001_create_coupons.php`. **Não recriar o esquema existente.** A migration conflitante de 27/10 foi retirada de `database/migrations`.

## Admin (Filament)

Em Vendas → Cupons de desconto, os campos correspondem ao banco original:
- Código único (`code`, normalizado); descrição opcional.
- `discount_type`: `percentage` ou `fixed`; valor em `value` (percentual inteiro ou centavos, respectivamente).
- `scope`: `all`, `categories` ou `products`; pivôs existentes definem os itens contemplados.
- Opcional: subtotal mínimo `minimum_subtotal_cents`, teto `maximum_discount_cents`, `usage_limit`, validade e ativação.
- `allow_sale_items` determina se itens com preço promocional participam do cálculo.
- Histórico e reservas são preservados. O contador de reservas/usos é obtido de `coupon_claims`, não de `used_count`.

## Checkout

O botão Aplicar verifica o cupom com `QuoteCouponAction`, baseado nos itens e preços atuais calculados no backend. A sessão usa o token do checkout. Na criação do pedido, a validação é refeita após travar os produtos, e `ManageCouponUsageAction::reserve()` grava uso reservado e claim por CPF/CNPJ. A claim é única no banco. Um segundo pedido não pode usar o mesmo código com o mesmo CPF/CNPJ, mesmo com outro e-mail.

Um desconto incide somente sobre itens elegíveis, não sobre o frete. Um cupom que zere todo o subtotal é recusado pelo fluxo atual, pois o gateway hospedado exige itens positivos. A regra de frete sob consulta preserva desconto e reserva; a aprovação de pagamento consome a reserva uma única vez. A rejeição/cancelamento terminal libera claims reservadas. Consumos confirmados permanecem históricos.

## Produção

Sem novas migrations de cupom. Mantenha `.env`, `APP_KEY` e banco de produção; não rode `migrate:fresh`. O `APP_KEY` participa do hash dos documentos. Há um comando `php artisan saba:expire-coupon-reservations` agendado a cada 5 minutos, mas o cron precisa executar `php artisan schedule:run` a cada minuto. Essa rotina não libera claims com cobranças remotas pendentes.

Servidor sem Node: esta correção altera apenas PHP, Blade já existente, testes e documentação. Não exige novo build Vite; o build anterior é preservado no ZIP completo. Servidor sem Redis: use `CACHE_STORE=file` e `QUEUE_CONNECTION=database` quando apropriado.

Os testes de integração requerem Composer/vendor e banco isolado. Os fluxos de pagamento hospedado dependem da configuração e homologação do gateway do ambiente; não foram verificados diretamente nesta entrega.
