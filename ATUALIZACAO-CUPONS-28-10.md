# SABA Equipamentos — Correção do módulo original de cupons (28/10/2026)

## Diagnóstico da versão enviada

A aplicação recebeu uma segunda implementação de cupons sobre a migration original `2026_10_21_000001_create_coupons.php`. A migração de 27/10 tenta recriar `coupons` e introduz `coupon_redemptions`, enquanto o banco existente tem `coupon_claims` e `coupon_usages`. O Model, o admin e parte do checkout passaram a referenciar colunas inexistentes: `used_count`, `percentage`, `amount_cents`, `min_subtotal_cents`.

## Correção

- Model `Coupon` mapeia exclusivamente as colunas originais e as relações de produtos, categorias, usos e claims.
- Admin usa os campos `value`, `scope`, `minimum_subtotal_cents`, `maximum_discount_cents`, `allow_sale_items`, etc. Sem criar outro recurso de cupons.
- Checkout Livewire preserva Aplicar/Remover e mostra o desconto, com `QuoteCouponAction` verificando a elegibilidade real dos itens e a vigência.
- Criação de pedido reserva CPF/CNPJ e limite por cupom; aprovação de pagamento consome; cancelamento antes do pagamento libera. A correção reutiliza `ManageCouponUsageAction` e as cinco tabelas existentes.
- `routes/console.php` agenda liberação de reservas abandonadas e sem cobrança pendente a cada 5 minutos.
- O teste conflitante criado em 27/10 foi substituído por uma suíte de regressão que usa a estrutura original.
- A migration antiga de 21/10 é **mantida sem alterações**. A migration duplicada de 27/10 é desativada/arquivada, não executada.

## Arquivos entregues

- `saba-ecommerce-completo-corrigido.zip`: projeto do ZIP fornecido, com o hotfix aplicado. Contém `composer.json`, `composer.lock`, `public/build`, mas **não** contém `.env`, `vendor` ou `node_modules`.
- `saba-ecommerce-atualizacao.zip`: somente as alterações, para atualizar a instalação existente.
- `INSTALAR-ATUALIZACAO.sh`: script de aplicação do ZIP incremental com backup e isolamento da migration duplicada. Não executa migrations ou altera `.env`.

## Instalação incremental

Na máquina local, fora do banco de produção, teste primeiro:

```bash
cd /caminho/do/projeto
# Extraia o script do pacote (ou utilize a cópia do arquivo entregue):
unzip -p /caminho/saba-ecommerce-atualizacao.zip INSTALAR-ATUALIZACAO.sh > /tmp/instalar-cupons-saba.sh
bash /tmp/instalar-cupons-saba.sh /caminho/saba-ecommerce-atualizacao.zip
php artisan optimize:clear
php artisan migrate:status
php artisan test --filter=CouponCheckoutTest
```

Se a homologação passar, atualize o servidor usando **o pacote incremental**, não copiando todo o ZIP do projeto por cima de arquivos locais. Os registros existentes de cupons não são alterados.

## Produção sem Redis/Node.js

- Não é necessária nova migration de cupons. Evite `php artisan migrate --force` até conferir outras migrations pendentes.
- Não precisa executar `npm run build` para este hotfix, pois não altera os assets. A pasta `public/build` é preservada.
- Mantenha `CACHE_STORE=file`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database` (ou os drivers já homologados).
- Preserve `.env`, `APP_KEY`, imagens, logs e storage. O `APP_KEY` é utilizado no hash de CPF/CNPJ.
- Cron a cada minuto: `php /caminho/do/artisan schedule:run`.

## Escopo de validação

Foi conferida a sintaxe PHP e a composição dos ZIPs. O PHPunit/Laravel precisa de `vendor` e de um banco de teste; não foi executado neste ambiente. Pagamentos hospedados (especialmente Asaas) requerem homologação com a configuração real, pois o ZIP enviado também contém divergências antigas entre DTOs e o adapter Asaas, fora do escopo deste hotfix. **Não publique sem validar pagamento de teste/sandbox.**
