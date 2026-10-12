# Filament Back Office

Filament is the administrative UI only. All business behavior belongs in Actions/domain services.

## Planned navigation

### Dashboard

Initial useful metrics:

- today's orders
- awaiting payment
- paid/processing
- monthly sales
- recent orders
- top-selling products (after enough data exists)

### Catalog

- Banners
- Categories
- Products

### Sales

- Customers
- Orders

### Finance

- Cash flow

### Configuration

- Store settings
- Shipping providers
- Payment gateway

## Product resource

Expected admin fields:

- name
- SKU/code
- category
- short/full description
- images and order
- YouTube URL
- availability
- cost price
- selling price
- promotional price
- stock when enabled
- shipping dimensions/weight
- active
- featured
- WhatsApp quote enabled

Validation must reject a promotional price that is not lower than regular price.

## Security

- Only admin users authenticate into Filament.
- Customer accounts are separate.
- Do not display provider secrets after initial entry unless explicitly needed.
- Prefer environment-backed secrets initially; introduce encrypted persisted credentials only with a concrete operations requirement.


## Branding do painel

- Nome: SABA Equipamentos Serigráficos
- Logo: `public/images/saba-logo.jpeg`
- O logo deve aparecer na autenticação e no painel administrativo.
- Cor primária alinhada ao vermelho da marca SABA.
- Não substituir o logo por texto quando o arquivo estiver disponível.

## Ambiente local

O administrativo fica disponível em `http://localhost:8081/admin`.

## Catalog administrativo implementado

- Categorias: `/admin/categories`; produtos: `/admin/products`.
- Resources e Pages delegam escrita às Actions do Catalog. Não há edição direta em colunas nem exclusão em massa que contorne essas Actions.
- O formulário aceita valores em reais (`12.900,00`); `BrazilianMoney` converte usando strings e inteiros. A validação de preços continua nas Actions e em `ProductPricing`.
- Na edição, o custo é carregado explicitamente para o formulário administrativo, pois permanece oculto na serialização pública do model.
- Uploads múltiplos usam o disco Laravel `public`, diretório `products`, nomes gerados e limite de 5 MB por foto (JPG, PNG, WebP). Execute `./vendor/bin/sail artisan storage:link` para servir as imagens.
- Salvar o formulário adiciona as fotos; na edição, a galeria permite definir a principal, alterar a ordem numérica e excluir associações. A ordem de exibição segue a regra do domínio: principal, ordem, ID.
- Arquivos físicos são preservados ao excluir associações, conforme a decisão atual do domínio. Uploads de formulários abandonados ou rejeitados também podem permanecer no disco; limpeza física não faz parte desta etapa.
- O middleware do painel fixa `pt_BR`, inclusive nas requisições Livewire. Mensagens de validação usadas pelo catálogo estão em `lang/pt_BR/validation.php`.
- A autenticação existente continua válida: `User` representa os administradores, sem cadastro público. Atualmente o Filament permite esse model apenas no ambiente `local`; acesso em outros ambientes exige definir `FilamentUser::canAccessPanel()` antes da implantação.

## Tema visual SABA

- Tema oficial: `resources/css/filament/admin/theme.css`, registrado com `viteTheme()` no `AdminPanelProvider` e como entrada do Vite.
- Todas as páginas administrativas usam `maxContentWidth(Width::Full)`. O CSS define margens laterais de 24 px a partir de 1024 px e 16 px nas telas menores; tabelas e relation managers herdam a largura da página.
- Sidebar de 248 px. A navegação do MVP é organizada em Painel, Catálogo, Vendas e Configurações. O e-commerce não expõe um módulo de fluxo de caixa; indicadores comerciais usam Pedidos e Payments como fonte.
- Painel de Controle estende o Dashboard nativo apenas para ajustar título e grupo de navegação.
- Primary: paleta vermelha gerada a partir de `#dc2626`; estado ativo suave `#fef2f2`, texto/ícone ativo `#b91c1c`. Canvas `#f6f7f8`, superfície `#ffffff`, texto `#27272a`, borda `#e4e4e7`.
- Cores semânticas nativas: Green (sucesso), Amber (atenção), Red (erro), Zinc (neutro).
- Inter Variable local fornecida pelo Filament; nenhum carregamento de fonte externa. Dark mode desabilitado.
- Formulários de produto usam uma coluna abaixo de 1024 px; no desktop, Informações/Disponibilidade usam duas, Comercial/Publicação três e Transporte quatro. Descrições ocupam a linha inteira.
- O login mantém o layout centralizado nativo com card de largura Medium, logo existente e fundo sutil. O mesmo logo é usado como favicon.
- Para compilar alterações: `./vendor/bin/sail npm run build`. Após mudanças de configuração: `./vendor/bin/sail artisan optimize:clear`.

## Convenção permanente: formulários em cards

Todo formulário administrativo deve agrupar semanticamente os campos em `Section` ou `Fieldset` do Filament com aparência de card. Mesmo formulários pequenos precisam de ao menos um card. Nenhum campo deve ficar solto diretamente sobre o fundo cinza da página.

- Usar componentes contidos, com fundo branco, borda cinza clara de 1 px, raio de 8–12 px (10 px no tema), padding confortável e espaçamento vertical consistente. Preferir bordas sutis a sombras; não usar `contained(false)` para o agrupamento principal.
- O tema compartilhado aplica a mesma superfície às Sections e aos Fieldsets. Preservar o padding e o espaçamento nativos do Filament.
- Dentro dos cards, usar grid responsivo: uma coluna em mobile e duas, três ou quatro no desktop, conforme os campos. Descrições, editores e uploads ocupam a largura total com `columnSpanFull()`.
- Botões finais podem ficar fora dos cards, imediatamente abaixo do último card, mantendo as ações nativas de criar, salvar, criar outro e cancelar.
- A convenção vale também para formulários de ações e para todos os Resources atuais e futuros: Categorias, Produtos, Banners, Clientes, Pedidos, Configurações e Usuários.

### Agrupamentos de referência

Categoria usa um card **Informações da categoria** com Nome, Slug, Descrição, Imagem de destaque opcional, Ordem e Ativo; duas colunas no desktop, descrição e upload em largura total e uma coluna no mobile. O upload tem preview nativo, aceita JPG/PNG/WebP até 5 MB e usa o disco `public`, diretório `categories`, com nomes gerados e proteção contra caminhos arbitrários. A listagem exibe miniatura ou “Sem imagem”. Substituir/remover a associação preserva os arquivos físicos, conforme o padrão do Catalog.

Produto usa os seguintes cards:

| Card | Campos | Colunas no desktop |
| --- | --- | --- |
| Informações | Nome, Slug, SKU, Categoria, Descrição curta, Descrição | 2; descrições em largura total |
| Mídia | Fotos, Vídeo demonstrativo | 1 |
| Comercial | Preço de custo, Preço de venda, Preço promocional | 3 |
| Disponibilidade | Disponibilidade, Estoque | 2 |
| Transporte | Peso, Altura, Largura, Comprimento | 4 |
| Publicação | Ativo, Destaque, Cotação WhatsApp | 3 |

O formulário da ação de ordenar fotos usa o card **Ordenação da foto**. Esses agrupamentos são apenas de apresentação; não alteram validações, Actions ou regras de negócio.

## Convenção permanente: moeda brasileira

Todos os valores monetários administrativos usam `BrazilianMoneyInput` (`app/Filament/Forms/Components/BrazilianMoneyInput.php`). Produto aplica o componente a custo, venda e promoção, com prefixo `R$`, milhares separados por ponto e duas casas decimais separadas por vírgula.

- Digitação por centavos: `1` → `0,01`; `100` → `1,00`; `129990` → `1.299,90`; `1250000` → `12.500,00`.
- A máscara preserva a posição relativa do cursor e aceita colagem de valores brasileiros, inclusive com `R$`. Caracteres inválidos são bloqueados; limpar o campo promocional produz `null`.
- O componente converte centavos para o estado visual ao carregar e usa `BrazilianMoney::parse()` para devolver inteiros ao salvar. A conversão antes existente nas Pages foi removida. O custo oculto no model continua carregado explicitamente apenas no formulário administrativo.
- A máscara opera sobre strings; a conversão e os cálculos usam centavos inteiros, sem `float`, `Number` ou `parseFloat`. O banco permanece com suas colunas inteiras existentes.
- Nas tabelas, usar `BrazilianMoney::display()` para exibir `R$ 1.299,90`, nunca centavos crus ou notação estrangeira.
- No domínio Catalog, custo aceita zero, venda é obrigatória e maior que zero, e promoção é opcional, positiva e estritamente inferior à venda. Promoção igual ou superior retorna: “O preço promocional deve ser menor que o preço de venda.”
- Pedidos, frete, descontos e pagamentos deverão reutilizar estes componentes; não criar máscaras ou conversores independentes.

Verificação da máscara: `./vendor/bin/sail node --test tests/JavaScript/brazilian-money-input.test.mjs`. A suíte PHPUnit cobre hidratação, persistência em centavos, listagens, validação e edição repetida.

## Banners e configurações gerais

- **Configurações → Banners** (`/admin/banners`): listagem em largura global Full, preview desktop, título, ativo, ordem e datas; filtros Ativo/Inativo e Vigente. Formulário em cards Informações, Imagens e Publicação.
- Upload desktop obrigatório e mobile opcional no disco public, diretório banners. JPG/PNG/WebP até 5 MB; previews nativos, proteção contra caminhos arbitrários e retomada após erros de validação. Exclusão/replacement preservam arquivos físicos, conforme o padrão atual do Catalog.
- **Configurações → Configurações da loja** (`/admin/store-settings`): página única com cards Identidade, Contato, Endereço, Redes sociais, SEO e Frete; botão Salvar imediatamente abaixo. Abrir a página não grava dados; o primeiro salvamento cria a configuração.
- A configuração usa colunas explícitas e restrição de unicidade no banco. Nome da loja é obrigatório; demais campos são opcionais. Telefones preservam o formato informado; preencher WhatsApp com país e DDD.
- Persistência delegada a CreateBannerAction, UpdateBannerAction, DeleteBannerAction e UpdateStoreSettingsAction. Não há implementação de storefront nem integrações nesta etapa.

## Descrição rica dos produtos

`description` usa o RichEditor nativo, em largura total dentro da Section Informações. A coluna TEXT existente armazena HTML; não há migration, editor externo, anexos ou custom blocks. Ferramentas: H2/H3, negrito, itálico, sublinhado, listas, citação, links e desfazer/refazer. Emojis são texto. `ProductDescription` sanitiza o conteúdo na hidratação/deidratação do formulário e novamente na renderização pública, permitindo apenas texto e estrutura editorial. Scripts, eventos, estilos arbitrários, imagens, iframes, SVG e esquemas de links perigosos não são renderizados. A sanitização usa o componente Symfony já instalado, sem mudar o sanitizador global do Filament.
