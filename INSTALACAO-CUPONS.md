# Instalação — correção de cupons do SABA (28/10/2026)

## O banco existente é a fonte de verdade

**Não execute** `migrate:fresh`, `rollback` de cupons, `DROP TABLE`, nem a migration duplicada `2026_10_27_000001_create_coupons_and_redemptions.php`. O módulo original usa 5 tabelas. Nenhuma migration nova foi criada para corrigir a tela.

## No computador local

1. Faça backup dos arquivos atuais e do banco de dados.
2. Extraia o ZIP **incremental** na raiz do projeto ou utilize o script de atualização fornecido (ele faz backup dos arquivos substituídos).
3. Execute `php artisan optimize:clear`.
4. Em banco de **testes isolado**, rode `php artisan test --filter=CouponCheckoutTest` e a suíte geral. Não rode `RefreshDatabase` em produção.
5. Para os testes de interface, use o checkout com produto comum e cupom cadastrado no Filament.

## Em produção, sem Node e sem Redis

Envie apenas o ZIP incremental ou faça deploy da versão Git revisada. Preserve `.env`, `APP_KEY`, `composer.json`, `composer.lock`, fotos e arquivos de storage. Caso existam arquivos antigos duplicados, execute o script `INSTALAR-ATUALIZACAO.sh` **antes** de executar migrations pendentes. O script extrai a atualização, faz backup local e arquiva a migration de 27/10 caso esteja na raiz de `database/migrations`.

Após a instalação, execute:

```bash
php artisan optimize:clear
php artisan migrate:status
php artisan route:list --path=checkout
```

Não é preciso executar `php artisan migrate --force` para esta correção de cupons. Se houver outras migrations pendentes, revise-as separadamente. O código Blade/PHP não exige novo build do Vite. Se houve alterações de assets em outras funcionalidades, compile localmente e envie `public/build`.

Mantenha o cron `* * * * * php /CAMINHO/artisan schedule:run` operando em produção. Ele libera reservas de cupons sem pagamento pendente após 24h.

## Validação

Verifique: cupom percentual, valor fixo, validade, categoria/produto, itens já em promoção, limite total, CPF/CNPJ repetido, remoção no checkout, frete assistido, cancelamento e aprovação de pagamento. Confirme integrações reais (Asaas) em sandbox antes de aceitar cobranças reais.
