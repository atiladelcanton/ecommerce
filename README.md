# SABA Equipamentos — importação CSV e crawler por URL

Pacote **para integrar no repositório Laravel 13 do SABA**, não um projeto Laravel independente. Os arquivos PHP foram verificados por `php -l`. A execução integrada com o banco do SABA depende da aplicação real, que não foi enviada.

## Estrutura e decisões

- `saba:catalog:import`: importa produtos, associa **categorias preexistentes por nome/slug**, baixa imagens para `storage/app/public/products/imported/...` (no disco `public`) e grava os caminhos relativos em `product_images`.
- `saba:catalog:crawl`: recebe **URL pública** de categoria/produto, coleta HTML/JSON-LD + links de produto, exporta três CSVs e chama o mesmo importador. Para a home da Tele Silk, o mapa de categorias permite examinar as três seções originalmente coletadas.
- `catalog_import_sources`: guarda a URL de origem, identidade idempotente, metadados/preço original e `product_id`.
- `catalog_import_options`: mantém as **opções não verificadas** vinculadas ao registro de origem. **Não** cria `product_variants`, que exigem combinações completas e SKUs/preços confiáveis.
- Todos os produtos **novos** entram inativos, `availability=unavailable`, estoque 0, custo 0, e preço original em centavos (inclusive preço 0 como indicador de ausência de referência). **Não ativar sem ajustar preço/custo/estoque/medidas e conferir direitos das imagens.**
- Reimportações preservam produtos já alterados no administrativo: não reescrevem preço/custo/estoque/status, não excluem fotos. `--update-text` atualiza apenas textos de produtos importados ainda inativos.
- Nunca modifica produtos preexistentes apenas por slug. Se houver colisão de slug cria novo nome com sufixo de origem; SKU já usado não é duplicado.
- O crawler é **conservador**: páginas que dependem exclusivamente de JavaScript, Cloudflare, API privada, autenticação ou HTML não reconhecido podem retornar informações incompletas. Não tenta contornar proteções do site. Respeita `robots.txt`, limita páginas/produtos e atrasa requisições.
- Não há download de imagens neste pacote: apenas URLs para serem baixadas **no servidor da loja ao executar o Artisan**.

## Instalação

1. Faça backup do banco e do diretório `storage/app/public`.
2. Na **raiz do projeto SABA**, copie os conteúdos das pastas `app/`, `database/`, `config/`, `tests/` e `import-data/`, preservando seus caminhos. A pasta `import-data/` será criada na raiz com os CSVs de exemplo.
3. Confirme extensões PHP `fileinfo`, `mbstring`, `dom`/`xml` (DOM necessário para detectar opções em páginas HTML; JSON-LD e links funcionam sem DOM), e acesso HTTP HTTPS.
4. Verifique as categorias cadastradas: `Tinta Base Água`, `Tinta Plastisol`, `Berços`, `Máquinas de Serigrafia`, `Acessórios`. **IDs podem variar**, pois o importador confirma a identidade pelo nome, não pelo número isolado.
5. Execute a migration **incremental** e vincule o storage público:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan storage:link
./vendor/bin/sail artisan list | grep 'saba:catalog:'
```

Laravel 13 costuma descobrir automaticamente as classes em `app/Console/Commands`. Se os comandos não aparecerem, registre as duas classes através de `withCommands([...])` em `bootstrap/app.php` conforme o padrão já usado no projeto.

Se estiver em hospedagem sem Sail, substitua `./vendor/bin/sail artisan` por `php artisan`.

## Importar os três CSVs enviados

Primeiro simule (não altera o banco, nem faz download):

```bash
./vendor/bin/sail artisan saba:catalog:import import-data/telesilk_produtos_ecommerce.csv \
  --images=import-data/telesilk_imagens.csv \
  --options=import-data/telesilk_opcoes_variacoes.csv \
  --dry-run
```

Para realmente cadastrar e baixar as imagens:

```bash
./vendor/bin/sail artisan saba:catalog:import import-data/telesilk_produtos_ecommerce.csv \
  --images=import-data/telesilk_imagens.csv \
  --options=import-data/telesilk_opcoes_variacoes.csv
```

Opcional: `--no-images` para importar apenas os registros, `--update-text` para atualizar textos de rascunhos já importados. Execute novamente após falha de rede para completar imagens sem duplicar vínculos.

## Crawler: informar a URL e salvar automaticamente

### Uma categoria de qualquer loja com páginas HTML reconhecíveis

```bash
./vendor/bin/sail artisan saba:catalog:crawl \
  'https://www.telesilkonline.com.br/tintas-plastisol/plastisol' \
  --category='Tinta Plastisol' --max-products=100
```

O crawler exportará os três CSVs em `storage/app/catalog-imports/crawl-.../` e, por padrão, importará no banco + salvará imagens no `public` disk. Use `--no-import` para somente coletar, ou `--dry-run` para coletar e simular a importação.

### Home da Tele Silk, com mapeamento das 3 categorias coletadas

```bash
./vendor/bin/sail artisan saba:catalog:crawl \
  'https://www.telesilkonline.com.br/' \
  --map=config/telesilk-categorias.json \
  --max-products=100 --delay-ms=900
```

O mapa diferencia **Berços**, **Máquinas** e **Acessórios** na mesma página de equipamentos. Para novos fornecedores, crie um JSON nesse mesmo formato com as URLs de categoria (`path`) e nome de categoria SABA (`category`). As regras `match` são substrings de nomes de produtos, revisáveis e explícitas.

### Uma página de produto

```bash
./vendor/bin/sail artisan saba:catalog:crawl \
  'https://www.telesilkonline.com.br/produto/hidrocryl-mix-248' \
  --category='Tinta Base Água' --max-products=1
```

Opções: `--max-pages=10`, `--max-products=100`, `--delay-ms=800`, `--no-images`, `--no-import`, `--dry-run`. Outros padrões de URL podem usar `--product-pattern="~\/loja\/[^/]+~"` (regex PHP sobre o caminho). Para uma URL de home é necessário o mapa com caminhos específicos, não um único slug geral.

## Conferência do banco

```sql
SELECT c.name, COUNT(*) AS produtos_importados
FROM catalog_import_sources src
JOIN products p ON p.id = src.product_id
JOIN categories c ON c.id = p.category_id
GROUP BY c.id, c.name;

SELECT p.id, p.name, p.price, p.is_active, p.availability,
       COUNT(DISTINCT i.id) AS fotos,
       COUNT(DISTINCT o.id) AS opcoes_a_conferir
FROM catalog_import_sources s
JOIN products p ON p.id = s.product_id
LEFT JOIN product_images i ON i.product_id = p.id
LEFT JOIN catalog_import_options o ON o.catalog_import_source_id = s.id
GROUP BY p.id, p.name, p.price, p.is_active, p.availability;
```

**Atenção:** `source_options_count` do CSV (264 agregadas) descreve contadores de opções na vitrine, não o número de combinações individuais válidas. O CSV de opções tem 107 registros, alguns vazios/informativos, dos quais 93 trazem característica/valor recuperados. As opções coletadas ao vivo podem ter cobertura diferente.

## Testes locais no repositório

```bash
./vendor/bin/sail artisan test --filter=CatalogImportTest
./vendor/bin/sail artisan test --filter=CatalogHtmlExtractorTest
./vendor/bin/sail artisan test
./vendor/bin/sail pint --test
```

O download remoto usa HTTPS/HTTP, não segue redirect, limita tamanho e impede hosts privados no momento da validação DNS. Em produção, recomende também bloqueio de tráfego de saída para redes privadas na infraestrutura para mitigar DNS rebinding. Imagens podem estar sujeitas a direitos autorais/licença do fornecedor; confira permissão antes de publicá-las.
