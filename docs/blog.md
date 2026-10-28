# Blog opcional — SABA Equipamentos

## Escopo

Blog editorial exclusivo do e-commerce SABA, independente do catálogo, pedidos, clientes e demais integrações. Gerenciamento em **Admin → Conteúdo → Postagens do blog**. Configurações em **Admin → Configurações da loja → Blog**.

### Chaves de ativação

- `store_settings.blog_enabled` (padrão `false`): habilita `/blog`, `/blog/{slug}` e o link Blog no menu desktop/mobile e rodapé.
- `store_settings.blog_show_on_home` (padrão `false`): exibe a seção **Últimas do Blog**, apenas com `blog_enabled=true` e havendo postagens efetivamente publicadas.
- Com o blog desabilitado, ambas as rotas respondem 404. Conteúdo editorial permanece preservado no banco e no painel.

### Editor de postagens

- Título e slug único e amigável (automático ao criar, editável).
- RichEditor Filament 5 para conteúdo formatado: títulos H2–H4, negrito, itálico, sublinhado, tachado, listas, citações, links, desfazer/refazer.
- Resumo editorial opcional; imagem de destaque JPG/PNG/WebP até 5 MB em disco `public` (`blog/covers`); texto alternativo acessível.
- Categoria editorial opcional selecionável, com busca e ação **Criar categoria** no próprio formulário. Etiquetas (JSON, máximo 15) com sugestões de etiquetas já cadastradas e possibilidade de escrever novas. Título e descrição SEO opcionais.
- `draft`: invisível. `published` com `published_at` nulo: publicação imediata; data futura: publicação agendada, sem depender de cron.
- Listagem administrativa permite busca, filtro de status, edição, exclusão. Exclusão não remove automaticamente arquivos do disco.
- Conteúdo HTML é sanitizado pelo conversor já adotado no catálogo (scripts, embeds, iframes, estilos e imagens inline arbitrárias não são permitidos).
- O editor visual não oferece imagens embutidas no corpo; a imagem de destaque é o elemento visual da postagem.
- A publicação não depende da configuração de blog ativo. É possível preparar conteúdo ainda com o blog desligado.

### Público

- `/blog`: grade responsiva de postagens publicadas, ordenadas por `published_at`, paginação com 9 por página.
- `/blog/{slug}`: artigo completo com data, autor quando disponível, capa, categoria, etiquetas e metadados de SEO.
- A home apresenta as três postagens publicadas mais recentes quando a seção estiver habilitada.
- Rascunhos e postagens futuras retornam 404, inclusive mediante URL direta.

## Implantação

1. Aplicar o ZIP incremental na raiz do Laravel (mesclar diretórios, substituindo apenas arquivos coincidentes).
2. `php artisan migrate --force` (cria `blog_posts` e adiciona duas colunas a `store_settings`). Não usar `migrate:fresh`.
3. `php artisan storage:link` quando ainda não houver link de armazenamento público.
4. `php artisan optimize:clear && php artisan optimize` (com cache em `file`/database no servidor sem Redis).
5. Conferir configurações, criar rascunho, publicar, visualizar no desktop e mobile.

O novo CSS está em `public/css/blog.css` e é referenciado diretamente pelo layout — **não requer Node.js nem execução de `npm run build` no servidor**. O pacote preserva `public/build`, `composer.json`, `composer.lock` e `.env` existentes.

### Testes

`php artisan test --filter=BlogModuleTest`

Não adicionar dependências novas nem alterar migrations anteriores.

## Assistente de IA para postagens

O botão **Assistente de IA** aparece em Criar e Editar postagem no Filament. Reutiliza o provedor Groq, a chave criptografada, o modelo selecionado, as instruções da loja e o limite mensal configurados em **Configurações → Inteligência Artificial**. Não é necessário cadastrar outra chave nem instalar pacotes.

- **Escrever uma nova postagem:** informar tema/orientações; a IA propõe título, resumo, texto formatado e SEO.
- **Melhorar o texto existente:** aproveita o texto atual do editor (inclusive estado Livewire/TipTap) e permite pedir ajustes, selecionando tom e tamanho.
- A prévia exibe o resultado com campos selecionáveis. Aplicar altera **somente os campos escolhidos no formulário**, sem persistir dados. Para gravar, o usuário precisa clicar em **Salvar**. O assistente **não** altera slug, categoria, etiquetas, capa, autor, status ou data de publicação.
- O conteúdo HTML sugerido passa pelo sanitizador já utilizado no catálogo e no blog. O prompt proíbe invenção de dados técnicos, links, fontes e números não fornecidos.
- O uso é registrado em `ai_usage_logs` com `action = blog_content`, `product_id = null`, sem armazenar textos, prompts ou chaves. **Não requer migration nova**, novo serviço de IA, Redis ou Node.js; o build Vite já existente é preservado.
- A geração pela IA não equivale a revisão factual. O operador deve verificar e revisar o conteúdo antes de publicar.

## Categorias editoriais e etiquetas reutilizáveis (28/10/2026)

- Uma migration incremental cria `blog_terms` (tipo `category` ou `tag`, nome, slug único dentro de cada tipo). **Não altera a tabela `blog_posts`** nem as URLs públicas.
- A migration importa as categorias e etiquetas de **todas** as postagens existentes (incluindo rascunhos) para o vocabulário compartilhado. Não modifica o conteúdo dessas postagens.
- No cadastro/edição, **Categoria editorial** é um seletor pesquisável, com botão para criar uma nova categoria dentro de um modal. Uma categoria criada permanece disponível, mesmo que a postagem nunca seja salva.
- O campo **Etiquetas** mantém a entrada livre, agora com sugestões das já utilizadas. Novas etiquetas entram no vocabulário quando a postagem é salva; repetir a mesma etiqueta com caixa ou acentuação diferente não cria outra entrada.
- A Action `SaveBlogPostAction` registra os novos termos na mesma transação do artigo e salva a grafia padronizada em `blog_posts.category` e `blog_posts.tags`. O histórico do blog, a IA, SEO e a apresentação pública permanecem compatíveis.
- Excluir uma postagem **não exclui** categorias e etiquetas do vocabulário. Não há edição ou exclusão global de termos nesta etapa.
- **Implantação:** copiar os arquivos da atualização e executar `php artisan migrate --force` e `php artisan optimize:clear`. Não precisa de Redis, Node.js, Vite ou novas dependências Composer.
- Testes adicionados em `tests/Feature/Blog/BlogModuleTest.php`: cadastro automático, reutilização sem duplicação, preservação após excluir artigos e rejeição sem gravar termos quando a postagem falha na validação.
