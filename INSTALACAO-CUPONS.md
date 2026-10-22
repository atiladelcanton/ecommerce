# Instalação — Cupons digitados SABA

## Proteja sua instalação

Faça backup do banco e dos arquivos antes de copiar o hotfix. Não substitua `.env`, `APP_KEY` ou bancos SQLite/MySQL. `APP_KEY` deve permanecer inalterado, porque ele é usado para derivar os hashes de CPF/CNPJ.

## Instale

Copie o conteúdo do ZIP sobre a raiz do projeto e execute:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail artisan queue:restart
./vendor/bin/sail artisan test --filter=CouponCheckoutTest
./vendor/bin/sail artisan test
./vendor/bin/sail pint --test
./vendor/bin/sail npm run build
```

Mantenha cron `schedule:run` e worker `queue:work` em produção; em dev use `schedule:work` e `queue:work` em terminais separados.

## Uso

1. Admin → Catálogo → Cupons de desconto → Novo cupom.
2. Defina código (ex.: `BEMVINDO10`), benefício e produtos/categorias elegíveis.
3. Configure limites, datas e ativação.
4. No carrinho/checkout o comprador digita o código e clica **Aplicar**. Um CPF/CNPJ não pode reutilizá-lo após aprovação.
5. Veja o código e o desconto no detalhe do pedido.

O pacote mantém Mercado Pago arquivado e Asaas como gateway de novas compras; não altera integrações/logística.

## Estado de validação

Sintaxe PHP e testes JavaScript verificados sem dependências externas. PHPUnit e build precisam ser executados no ambiente Sail com `vendor/` e `node_modules` instalados. Faça homologação com checkout sandbox e webhooks para os estados aprovado, expirado e recusado antes de venda real.
