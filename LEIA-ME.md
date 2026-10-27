# SABA — Hotfix v3: opções encontradas, variantes não geradas

Esta atualização é **incremental** para o projeto SABA que já possui `saba:catalog:urls` e a migration de `catalog_import_sources` / `catalog_import_options`. Não altera o banco nem imagens. Não é o projeto Laravel completo.

## Sintoma corrigido

A homologação informa:

```text
[COLETA] ... QUADRO ... => Telas e Quadros; ... opções: 8; variantes completas: 0
```

Já no Tecido aparecem `opções: 18; variantes completas: 18`. Isso revela que o problema é a **conversão de opções em rascunhos**, e não que a coleta inteira falhou.

### Mudanças

1. Lê os atributos `data-attribute-name` / `data-items` de `.prod-option` diretamente do HTML (com suporte mesmo sem extensão DOM no PHP CLI), sem depender de inputs renderizados.
2. Reconciliará com as opções já encontradas pelo `CatalogHtmlExtractor` (ex.: `<select name="attributes[Tamanho]">`) quando o extrator detalhado não gerar variantes.
3. **Não** formará combinações cartesianas para vários atributos independentes não comprovados; esses valores continuam disponíveis em `catalog_import_options`.
4. Gera variantes inativas, sem preço de variante, com estoque zero e SKU interno `SABA-*`; reimportação apenas preenche ausências e preserva alterações comerciais.
5. Mantém extração dos 18 itens do Tecido e as sete tintas previamente configuradas.
6. Testes adicionados para recuperação, seletor Vnda, atributos independentes, reimportação e `--dry-run`.

## Implantação recomendada (evita conflitos Git na hospedagem)

**Na máquina de desenvolvimento**, abra o projeto Laravel e extraia o ZIP sobre a raiz do projeto:

```bash
unzip -o SABA_HOTFIX_VARIACOES_V3.zip -d .
./vendor/bin/sail artisan test --filter=ImportCatalogUrlsTest
./vendor/bin/sail pint --test
git add app/Infrastructure/Catalog/CompleteProductVariationExtractor.php \
  app/Actions/Catalog/ImportCatalogUrlsAction.php \
  tests/Feature/Catalog/ImportCatalogUrlsTest.php
git commit -m "fix: recupera variacoes de seletores e data-items"
git push origin main
```

**No servidor de homologação** (após publicar via Git):

```bash
git pull --ff-only origin main
php artisan optimize:clear
php artisan saba:catalog:urls \
  'https://www.telesilkonline.com.br/produto/quadro-de-madeira-pinus-medida-externa-177' \
  --dry-run --no-images
```

O log de coleta esperado é **`opções: 8; variantes completas: 8`**. Observação: a tabela de métricas do dry-run continua exibindo `variant_drafts: 0`, porque não são gravados registros na simulação. A contagem *planejada* aparece na linha `[COLETA]`.

Se estiver correto, faça a importação efetiva:

```bash
php artisan saba:catalog:urls \
  'https://www.telesilkonline.com.br/produto/quadro-de-madeira-pinus-medida-externa-177' \
  --no-images
```

**Se o produto já estiver ativo, o importador deliberadamente não acrescenta variações.** Desative-o pelo Filament, importe e revise as novas variantes antes de reativar. Evite modificar produtos com pedidos ou reservas existentes sem avaliar os vínculos.

Confirme no MySQL:

```sql
SELECT p.id, p.name, p.is_active, COUNT(pv.id) AS variantes
FROM products p
LEFT JOIN product_variants pv ON pv.product_id = p.id
WHERE p.slug LIKE 'quadro-de-madeira-pinus-medida-externa-177%'
GROUP BY p.id, p.name, p.is_active;
```

## Verificações deste pacote

- `php -l` dos três arquivos PHP: aprovado.
- Teste isolado do extrator executado para `data-items` do Pinus (8), fallback por opções, múltiplos atributos e 18 escolhas do Tecido, sem Laravel e sem DOM: aprovado.
- Suíte completa PHPUnit/Laravel **não executada aqui**: o ambiente desta geração não contém `vendor/` e extensões PHP DOM/mbstring. Execute no Sail antes do deploy.
- Sem migrations novas nem mudança em CSS/JS, portanto não exige `npm run build` para este hotfix.
