# SABA Equipamentos — importação por URLs (homologação)

Pacote incremental para o projeto Laravel 13 / PHP 8.3+ SABA. Não é um e-commerce inteiro: **extraia sobre a raiz de seu projeto existente**. Não apaga categorias, produtos, pedidos, clientes ou histórico.

## O que faz

- `saba:catalog:urls`: importa **URLs individuais de produto**, informadas como argumentos, em arquivo texto ou por um preset de sete tintas.
- Lê o HTML público e prioriza `class="short-description"` para a **descrição técnica**. SEO fica separado; preserva parágrafos/quebras de linha.
- Normaliza referências de marca **Tele Silk/Telesilk** para **SABA** nos textos de catálogo, nomes e alt-texts. URLs de origem e de CDN não são alteradas porque precisam apontar para os arquivos verdadeiros.
- Vincula pelo `catalog_import_sources.source_key` (hash de host+caminho): executar novamente **não duplica** o mesmo produto. Nunca sequestra um produto existente sem proveniência da URL.
- Cria as categorias reconhecidas caso ainda não existam: Tinta Base Água, Tinta Plastisol, Berços, Máquinas de Serigrafia, Secagem e Cura, Telas e Quadros, Rodos, Acessórios. Também pode criar categoria informada explicitamente com `--category`.
- Produto sem categoria confiável vai para **Sem definicao** (`sem-definicao`), inativa. A regra de visibilidade do site **exclui sempre** essa categoria e TODOS os produtos dela: home, menu, busca, listagem, detalhe (404), carrinho e produtos relacionados. Mesmo que a categoria seja reativada acidentalmente, permanece oculta.
- Gera SKUs internos determinísticos `SABA-TA-*`, `SABA-TP-*`, etc. Para produtos importados anteriormente e ainda inativos, converte SKUs externos para internos; não altera SKUs de produtos comerciais ativos.
- Baixa imagens JPG/PNG/WebP (até 5 MB cada) com validação para `storage/app/public/products/imported/...`; mantém galerias e foto principal em `product_images`, repetível sem duplicação. Tenta foto grande antes da miniatura, quando o CDN admite.
- Coleta escolhas de cor/tamanho em `catalog_import_options`. Os sete produtos iniciais têm configurações revistas para **gerar apenas variações inativas** no admin. Essas combinações e preços NÃO são confirmados pelo fornecedor. Nas URLs novas com múltiplos atributos, somente as opções ficam no staging, sem fabricar combinações vendáveis.
- **Não importa preços comerciais, custo, peso, medidas ou estoque fictícios.** Novos produtos ficam inativos, `availability=unavailable`, `stock=0`, `cost_price=0`, `price=0`. É obrigatório revisar antes de qualquer venda.

## Instalação

Faça backup do banco e do código. Dentro da pasta do e-commerce SABA:

```bash
unzip -o /caminho/SABA_Homolog_Import_URLS.zip -d .
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan storage:link
```

Sem Sail, substitua `./vendor/bin/sail artisan` por `php artisan` no servidor, no ambiente com PHP 8.3+ e dependências Composer instaladas.

O PHP precisa da extensão **DOM** (`php-xml`) para recuperar `.short-description` e opções do HTML. Confirme com `./vendor/bin/sail php -m | grep -i dom`.

As tabelas `catalog_import_sources` e `catalog_import_options` estão incluídas por migration incremental. A migration mantém IDs e não modifica pedidos. **Não use `migrate:fresh`, `TRUNCATE` ou `DROP`** neste processo.

## Comandos

**Simular os sete produtos iniciais sem gravar arquivos/banco** (faz HTTP para extrair informações):

```bash
./vendor/bin/sail artisan saba:catalog:urls --preset=tintas --dry-run
```

**Importar as sete tintas como rascunhos não publicados:**

```bash
./vendor/bin/sail artisan saba:catalog:urls --preset=tintas --refresh-text
```

**Ou importar pelas sete URLs do TXT incluso:**

```bash
./vendor/bin/sail artisan saba:catalog:urls --file=import-data/urls-tintas-homolog.txt --refresh-text
```

**Importar novas URLs específicas** (não precisa alterar PHP nem configuração):

```bash
./vendor/bin/sail artisan saba:catalog:urls \
  'https://www.telesilkonline.com.br/produto/EXEMPLO-1' \
  'https://www.telesilkonline.com.br/produto/EXEMPLO-2'
```

Substitua os exemplos por URLs reais e existentes. Pode informar URLs de outros sites públicos, desde que tenham páginas HTML individuais acessíveis; a extração de variantes/imagens varia conforme o site. Não há navegação automática por categorias nem autenticação.

**Criar/corrigir explicitamente a categoria de um conjunto de URLs:**

```bash
./vendor/bin/sail artisan saba:catalog:urls --category='Tinta Base Água' \
  'https://www.telesilkonline.com.br/produto/tinta-hidrocryl-cromia-243'
```

**Importar sem baixar fotos:** adicione `--no-images`.

**Rebuscar títulos/descrições dos produtos ainda inativos:** adicione `--refresh-text`. O comando não sobrescreve o conteúdo editorial de produtos ativos nem seus preços, saldos, custos ou medidas.

### Mostrar na vitrine de homologação, sem permitir compras

Opcional. Para o cliente visualizar os produtos, mas SEM concluir pedidos, configure `APP_ENV=staging` ou `APP_ENV=homolog` (também aceita `local`, `homologation`, `testing`). Depois execute:

```bash
./vendor/bin/sail artisan saba:catalog:urls --preset=tintas --refresh-text \
  --publish-preview --demo-price-cents=9990
```

Requer confirmação interativa. O valor **R$ 99,90 é apenas ilustrativo**, não preço real obtido do fornecedor. O produto fica `is_active=1`, `is_featured=1` (aparece também na home) e `availability=unavailable` impede a compra. **Nunca use preço de demonstração para vender.** `Sem definicao` continua inativa/invisível mesmo neste modo. Para publicar de verdade, a equipe deve revisar e cadastrar os preços, custos, SKUs, combinações, fotos, pesos, dimensões e disponibilidade pelo administrativo.

Se a homologação utiliza `APP_ENV=production`, o modo demonstrativo é deliberadamente recusado. Você pode executar a importação em rascunho e revisar/publicar manualmente no Filament.

## Verificação

```bash
./vendor/bin/sail artisan test --filter=ImportCatalogUrlsTest
./vendor/bin/sail pint --test
```

Após a importação, confira os produtos em **/admin/products** e as categorias em **/admin/categories**. No banco, verifique `products`, `product_images`, `product_variants`, `catalog_import_sources` e `catalog_import_options`.

O sucesso do importador depende do HTML atual, do acesso à rede, da CDN e das proteções dos sites consultados. HTTP 403/429 e falhas de imagem são reportados — não há bypass de bloqueios. O parâmetro `--dry-run` não grava no banco nem baixa imagens, mas faz requisições ao site.

## Compatibilidade

Inclui apenas os arquivos novos/substituídos do SABA. Reutiliza as classes `CatalogHtmlExtractor`, `SafeCatalogHttp` e `RemoteCatalogImage` já existentes no projeto enviado no ZIP de 20/10/2026 ou instaladas com a importação anterior. Não exige pacotes Composer novos. Execute primeiro em banco de homologação, com backup. Reexecutar a mesma URL não sobrepõe alterações comerciais existentes.
