# SABA – Hotfix de banner e variações (23/10/2026)

Este pacote **incremental** deve ser aplicado sobre o projeto SABA que já recebeu o importador `saba:catalog:urls` do pacote anterior. Não contém `vendor`, `.env` nem banco de dados.

## Alterações

1. **Banner da home:** remove as alturas fixas que produziam margens ao usar `object-fit: contain`; obtém as dimensões da **primeira imagem efetiva do disco public** e aplica a mesma proporção à área do carrossel. Também usa o arquivo mobile quando cadastrado. Não estica nem corta a arte. Banners com proporções diferentes precisam ser padronizados para uma experiência consistente no mesmo carrossel.
2. **Opções completas do fornecedor:** um novo extrator procura seletores e opções com identificadores `variation`, `variant`, `variações`, etc., inclusive `<select>`, rádio e labels. Essas opções completas podem virar variações inativas em `product_variants`, além de serem preservadas em `catalog_import_options`.
3. **TECIDO (591):** usa as **18 opções completas publicamente observadas** na página em 23/10/2026. Caso as opções não apareçam no HTML devolvido ao Artisan, recorre a essa lista histórica apenas para essa URL. Desmembra cada combinação completa em `Material`, `Fios`, `Cor`, `Comprimento` e `Largura`; não cria outras combinações e não preenche preço ou estoque. A classificação de tecido/poliéster/nylon passa a **Telas e Quadros**.
4. **Reimportação:** encontra o produto original pela mesma URL e cria apenas variações inexistentes, preservando preço, custo e estoque existentes. Se o produto estiver **ativo**, salva opções para conferência, porém **não modifica variações**, evitando mudança inesperada na obrigatoriedade de seleção no checkout.

## Instalação (backup antes)

Na raiz do Laravel, depois de guardar cópia dos arquivos que serão sobrescritos:

```bash
unzip -o SABA_HOTFIX_BANNER_VARIACOES_2026-10-23.zip -d .
php artisan optimize:clear
```

Não há migration nova. O banco já deve conter `catalog_import_sources`, `catalog_import_options` e `product_variants` do pacote anterior.

### Atualizar o tecido já importado

**Primeiro, confira se o produto está inativo no Filament.** Se ele já estiver ativo, desative-o antes de reimportar as variações (e não ative novamente antes de revisar os preços, combinações e estoque).

```bash
php artisan saba:catalog:urls \
  'https://www.telesilkonline.com.br/produto/tecido-591' \
  --no-images
```

Sem alterações no banco:

```bash
php artisan saba:catalog:urls \
  'https://www.telesilkonline.com.br/produto/tecido-591' \
  --dry-run --no-images
```

A simulação exibirá `variantes completas: 18` quando as informações estiverem disponíveis. No comando real, espere `variant_drafts: 18` **se ainda não houver registros equivalentes**. Na segunda execução, espere `variant_drafts: 0`.

### Outras URLs

```bash
php artisan saba:catalog:urls 'https://fornecedor.com.br/produto/algum-produto'
```

Sem combinações explícitas verificáveis, o comando continua preservando opções isoladas para conferência, **sem fazer produto cartesiano automaticamente**. As demais opções de comando existentes foram mantidas.

### Compilar CSS para homologação

O arquivo `resources/css/app.css` foi alterado. **Na sua máquina, compile antes de enviar ao cPanel:**

```bash
./vendor/bin/sail npm run build
```

Envie os arquivos produzidos em `public/build/` para a homologação junto com os PHP e Blade, ou compile na homologação caso disponha de Node/Vite. Execute `php artisan optimize:clear`, depois atualize a página com Ctrl+Shift+R.

### Testes incluídos

```bash
./vendor/bin/sail artisan test --filter=ImportCatalogUrlsTest
./vendor/bin/sail artisan test --filter=HeroCarouselTest
./vendor/bin/sail pint --test
```

**Validação nesta entrega:** sintaxe PHP verificada; os testes integrados dependem de `vendor` e das extensões PHP do ambiente do projeto e não foram executados neste sandbox. Revise os arquivos modificados, principalmente `app.css`, se seu projeto tiver personalizações posteriores ao ZIP original.

### Consulta para conferir as 18 variações

```sql
SELECT pv.id, pv.name, pv.value, pv.sku, pv.is_active, pv.price, pv.stock
FROM product_variants pv
JOIN products p ON p.id = pv.product_id
WHERE p.slug LIKE 'tecido-591%'
ORDER BY pv.sort_order, pv.id;
```

Todas as variações novas devem ficar **inativas**, sem preço próprio (`NULL`) e com estoque zero. Para exibir/comercializar, revise também o preço geral (>0), disponibilidade e medidas para frete.
