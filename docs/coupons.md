# Cupons de desconto digitados — SABA

## Regras aprovadas

- Não há promoções automáticas. Apenas códigos informados deliberadamente no carrinho/checkout.
- Um cupom por pedido e **uma utilização por CPF/CNPJ**, independentemente de login, conta ou e-mail. Os documentos são normalizados e protegidos por hash HMAC persistente. Não existe verificação governamental da identidade: o cadastro do checkout precisa informar o próprio documento.
- Tipos: percentual inteiro ou valor fixo em centavos. Aplicação: toda a loja, categoria(s) ou produto(s), incluindo suas variações. Frete não participa do desconto.
- Por padrão, produtos com promoção já aplicada não acumulam desconto. É possível habilitar por cupom.
- Compra mínima, desconto máximo, vigência, ativação e limite global de utilizações são configuráveis.
- O desconto é recalculado no backend durante a transação de criação do pedido, com os mesmos preços/variantes usados nos snapshots. As informações enviadas pelo navegador nunca definem o valor final.
- O cupom aplicado é guardado temporariamente na sessão (`store.coupon_code`). A sessão é limpa após fechar o pedido ou limpar o carrinho.
- Pedidos guardam `coupon_code`, `coupon_id` e o valor concedido em `discount_cents`; a mudança posterior no cadastro do cupom não altera pedidos históricos.

## Uso único com checkout assíncrono

- `coupon_usages`: um histórico por pedido, com estados `reserved`, `consumed`, `released`.
- `coupon_claims`: uma reivindicação ativa/consumida única por cupom + HMAC do CPF/CNPJ. A constraint única previne disputa entre duas requisições simultâneas.
- Em pedido pendente ou pagamento remoto pendente, `coupon_claims` é mantida. A aprovação marca `consumed` e a reivindicação permanece (mesmo em caso de cancelamento comercial ou reembolso posterior, pois a campanha já foi usada).
- Recusa definitiva da inicialização, expiração/cancelamento confirmado pelo gateway ou cancelamento comercial **não pago** libera o uso. Falhas HTTP incertas não liberam.
- Em frete assistido e outros pedidos sem cobrança remota, o job `saba:expire-unused-coupons` remove reivindicações abandonadas após 24h. Aceitar posteriormente a cotação exige revalidar o cupom e re-reservar utilização, ou informar a impossibilidade.
- Repetir uma cobrança após recusa requer nova reserva do benefício, sem ultrapassar uso único ou limites globais.

## Painel Filament

**Catálogo → Cupons de desconto:** Código, descrição, tipo/valor, aplicação e vínculos, permitir promoções, compra mínima, limite máximo, quantidade total, vigência e ativação. Ao criar, o cupom começa inativo.

## Deploy

1. Backup do banco e do repositório.
2. Aplicar arquivos do pacote e executar `./vendor/bin/sail artisan migrate` (incremental).
3. `./vendor/bin/sail artisan optimize:clear` e `./vendor/bin/sail artisan queue:restart`.
4. Garantir o scheduler em operação (inclusive `saba:expire-unused-coupons`).
5. Executar `./vendor/bin/sail artisan test --filter=CouponCheckoutTest`, a suíte total, Pint e build antes de publicar.
6. Criar cupom inativo, testar em sandbox e ativar no painel apenas após validar.

A cobrança no Asaas recebe o total já descontado, representado por linha-resumo quando o desconto é aplicado para manter a soma exata dos itens. Não envolve API de cupons no gateway.
