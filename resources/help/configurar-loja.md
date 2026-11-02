## Onde configurar

Abra **[Configurações → Configurações da loja](/admin/store-settings)**. Os campos são divididos em blocos. Ao alterar algo, **clique em Salvar**: apenas abrir a página não grava nada.

## Identidade da empresa

1. **Nome da loja:** como sua marca será apresentada ao cliente.
2. **Razão social, CNPJ, inscrição estadual e CNAE:** preencha com dados oficiais. Esses dados podem ser necessários para identificar o remetente em operações de transporte e documentos fiscais.
3. Não use números fictícios em produção. Se sua empresa não possui inscrição estadual ou outro dado, verifique com o contador qual configuração se aplica.

## Logotipo e cores da loja

1. No bloco **Identidade visual**, selecione um logotipo em JPG, PNG ou WebP, de até **5 MB**. Prefira PNG/WebP com fundo transparente e proporção horizontal para o cabeçalho.
2. Escolha a **cor primária** para destaques, links e elementos importantes.
3. Escolha a **cor secundária** para faixas e elementos complementares.
4. Escolha a **cor dos botões**, que será aplicada às principais ações de compra.
5. **Clique em Salvar**. A logo cadastrada passa a aparecer tanto na **loja pública** (cabeçalho e rodapé) quanto no **painel /admin**, inclusive na tela de login. O nome exibido no painel também acompanha o campo **Nome da loja**.
6. Abra a loja pública e o painel em outras abas para conferir o resultado. Atualize as páginas após salvar.
7. Para voltar à cor padrão, deixe o campo de cor vazio. Se remover o logotipo, o sistema mostra **o nome da loja como texto**, em vez de uma logo de outra empresa.
8. Para trocar a imagem, envie a nova logo e salve novamente. A loja e o painel devem passar a exibir a mesma imagem.

> **Dica:** o logotipo deve estar em armazenamento público. Se a imagem não abrir, confira se o administrador técnico executou `php artisan storage:link`, se o arquivo foi salvo e se o diretório de arquivos está acessível. A imagem antiga ainda pode aparecer temporariamente por cache do navegador; atualize a página forçando o recarregamento.

A cor do texto dos botões é selecionada para manter contraste. Em algumas seções, a aparência também depende do template em uso.

> **Importante:** a escolha de tema fica em **[Configurações → Temas da loja](/admin/store-templates)** para quem tem permissão de administrar as configurações. O grupo Operacional não pode trocar o tema. A troca do tema não apaga nome, logotipo ou cores cadastrados.

## Contato, endereço e canais

- Informe telefone, e-mail e WhatsApp com DDI e DDD, por exemplo `55` + DDD + número. Esse número é usado pelos links de contato/cotação da loja.
- Preencha o endereço físico corretamente, inclusive UF, CEP e número.
- Preencha **CEP de origem** do frete: a integração logística usa esse local para calcular entregas.
- Use os links completos de Instagram, Facebook e YouTube, começando por `https://`.
- Contatos vazios não precisam aparecer no site: se um canal não existe, pode ser deixado em branco.

## SEO

**Título SEO** é o nome exibido nos resultados de busca. **Descrição SEO** é o resumo apresentado pelos buscadores quando eles optam por utilizá-lo. Evite textos repetitivos; descreva claramente o que a loja vende.

## Blog na loja

Existem duas opções independentes:

1. **Ativar blog no e-commerce:** disponibiliza menu Blog e páginas públicas dos artigos.
2. **Exibir “Últimas do Blog” na página inicial:** apresenta artigos recentes na home, mas somente se o blog estiver ativo e houver postagens publicadas.

Desativar o blog não exclui os textos escritos no painel.

## Como conferir a alteração

1. Clique em **Salvar**.
2. Abra a loja pública e atualize a página.
3. Confira nome, logo e botões no desktop e no celular. Abra também o `/admin` e veja se a marca do painel mudou.
4. Se o WhatsApp abrir número errado, revise DDI/DDD; se o frete falhar, revise principalmente o CEP de origem, peso e dimensões do produto.

## Ao instalar a loja em outro domínio: logo com erro 404

Se a loja foi copiada de outra instalação, é necessário configurar **cada instalação separadamente**. A logo é um arquivo da loja atual; ela não deve depender da pasta nem do domínio da loja anterior.

1. Avise o responsável técnico para verificar o `APP_URL` da instalação atual e o link `public/storage`.
2. Após enviar a logo, clique em **Salvar**. Se aparecer 404, tente abrir a imagem em outra aba para confirmar o erro.
3. O técnico pode executar `php artisan loja:diagnosticar` na pasta da loja para verificar o destino dos uploads e a existência do arquivo. Esse comando não altera dados.
4. O diretório `public/storage` deve apontar para `storage/app/public` **da mesma instalação**. Caso a cópia tenha trazido um link antigo, o técnico deve substituir somente o link simbólico, sem apagar as imagens armazenadas.
5. Se o painel de outra loja mostrar erro 403, o técnico deve conferir o banco de dados, as sessões e o grupo administrativo da conta. Alterar a logo não concede nem remove permissões.

> **Segurança:** não compartilhe `.env`, senhas ou tokens para solicitar ajuda. Nunca utilize `php artisan migrate:fresh` para resolver erros de logo ou de acesso.
