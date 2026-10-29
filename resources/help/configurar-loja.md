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
5. **Salve** e abra a loja pública em outra aba para conferir em computador e celular.
6. Para voltar à cor padrão, deixe o campo de cor vazio. Ao remover o logotipo personalizado, a loja volta ao logo padrão disponível no template.

A cor do texto dos botões é selecionada para manter contraste. Em algumas seções, a aparência também depende do template em uso.

> **Importante:** não existe seletor de templates para o lojista. Só a equipe técnica pode executar `php artisan switch:template --template=1` ou `--template=2` na instalação correspondente.

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
3. Confira nome, logo e botões no desktop e no celular.
4. Se o WhatsApp abrir número errado, revise DDI/DDD; se o frete falhar, revise principalmente o CEP de origem, peso e dimensões do produto.
