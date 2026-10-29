# Manual completo do administrador — SABA E-commerce

Este manual reúne os mesmos 27 tutoriais disponibilizados em **Admin → Ajuda**.

## Sumário

1. Primeiros passos e mapa do painel
2. Dados da loja e identidade visual
3. Acessos, senhas e proteção dos dados
4. Cadastrar e organizar categorias
5. Cadastrar produtos passo a passo
6. Variações, medidas e estoque
7. Preços e promoções
8. Banners: imagens, formatos e agendamento
9. Blog: artigos, categorias e IA
10. SEO, divulgação e campanhas
11. Cupons: cadastro e regras
12. Clientes, contas e endereços
13. Pedidos e mudanças de status
14. Pós-venda, devoluções e estornos
15. Meios de entrega e cotações
16. Gerar, imprimir e postar etiquetas
17. Ações da etiqueta e solução de falhas
18. Frete sob consulta e negociação
19. Pagamentos Asaas e cobranças
20. Criar conta e configurar o Asaas
21. Criar conta e conectar o Melhor Envio
22. Assistente de IA: Groq para produtos e blog
23. Dashboard, relatórios e aniversariantes
24. Dúvidas frequentes e solução de problemas
25. Situações práticas: do cadastro à entrega
26. Glossário dos termos do e-commerce
27. Checklists de abertura e operação

# 1. Primeiros passos e mapa do painel

## Para que serve este painel?

O painel é o centro de operação da sua loja: **Catálogo** organiza produtos, **Conteúdo** gerencia o blog, **Vendas** acompanha clientes, cupons e pedidos, **Configurações** cuida de identidade da loja, banners, frete, pagamentos e IA. **Ajuda** reúne os procedimentos de uso. O painel não é um sistema de emissão de notas fiscais nem substitui o financeiro da sua empresa.

## Ordem recomendada para abrir a loja

1. Vá a **Configurações → Configurações da loja** e preencha nome comercial, contato, WhatsApp, endereço e **CEP de origem do frete**. Confira CNPJ, razão social e dados de remetente.
2. Na mesma página, envie seu logotipo e ajuste cores, se desejar. As cores mudam o **site**, não a área administrativa.
3. Vá a **Catálogo → Categorias** e crie suas categorias antes de cadastrar os produtos.
4. Em **Catálogo → Produtos**, inclua pelo menos um produto completo: título, categoria, preço de venda, disponibilidade, fotos, peso e todas as dimensões da embalagem.
5. Em **Configurações → Banners**, publique uma imagem para a página inicial, se desejar.
6. Em **Configurações → Meios de Entrega**, configure **Melhor Envio** e/ou **retirada na loja**. Use o Sandbox até validar o fluxo de frete.
7. Em **Configurações → Meios de Pagamento**, conecte e teste a conta **Asaas**. Teste em Sandbox antes de habilitar cobranças reais.
8. Faça uma compra completa de teste: produto → carrinho → endereço → frete → checkout → recebimento de pagamento → preparação → envio/retirada → entrega.
9. Confira na loja pública as páginas de produto, carrinho, contato e seus dados comerciais.

### Onde encontro cada coisa?

| Quero fazer... | Acesse |
| --- | --- |
| Criar categoria | [Catálogo → Categorias](/admin/categories) |
| Cadastrar, editar ou consultar produto | [Catálogo → Produtos](/admin/products) |
| Criar banner | [Configurações → Banners](/admin/banners) |
| Alterar logo, cores, dados e blog | [Configurações da loja](/admin/store-settings) |
| Criar cupom | [Vendas → Cupons de desconto](/admin/coupons) |
| Escrever conteúdo | [Conteúdo → Postagens do blog](/admin/blog-posts) |
| Listar pedidos | [Vendas → Pedidos](/admin/orders) |
| Acompanhar por colunas | [Vendas → Pedidos Kanban](/admin/orders-kanban) |
| Configurar frete | [Meios de Entrega](/admin/shipping-methods) |
| Configurar pagamento | [Meios de Pagamento](/admin/payment-methods) |
| Ativar IA | [Inteligência Artificial](/admin/ai-settings) |

> **Atenção:** o visual da loja (template 1 ou 2) é selecionado pela equipe técnica, fora do painel. O lojista pode alterar sua marca e cores, mas não trocar o template.

## Três conceitos que evitam erros

- **Salvar não é necessariamente publicar.** Um produto inativo, cupom inativo ou artigo em rascunho continua no painel, mas não fica disponível para compras ou leitura pública.
- **Pedido pago não significa pedido enviado.** A aprovação do pagamento, a preparação da mercadoria, a geração de etiqueta e a postagem são etapas diferentes.
- **Conta de testes não é conta real.** Sandbox de Asaas e Melhor Envio usa credenciais próprias; valide as integrações antes de ativar produção.

## Quando pedir suporte técnico?

Procure o responsável técnico se aparecer falha de servidor (erro 500), suspeita de webhook não recebido, cobrança duplicada, etiqueta paga com resultado incerto, perda de acesso administrativo ou necessidade de alterar o template. Para erros de cadastro, consulte primeiro a seção correspondente deste manual.

# 2. Dados da loja e identidade visual

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

# 3. Acessos, senhas e proteção dos dados

## Quem deve acessar o administrativo?

O acesso em `/admin` é destinado somente a pessoas autorizadas a operar a loja. **Não compartilhe sua senha, sua sessão de navegador nem credenciais de integração.** Cada conta usada pela equipe deve ser identificável, para que seja possível investigar alterações ou acessos indevidos.

> **Limitação desta versão:** o painel não inclui, no módulo Ajuda, uma tela para cadastrar operadores com permissões granulares (por exemplo, “somente produtos” ou “somente pedidos”). A gestão de usuários administrativos e a revisão das permissões devem ser tratadas com a equipe responsável pela instalação. Não presuma que funções diferentes já possuem restrições específicas.

## Primeiro acesso: cuidados importantes

1. Use o endereço oficial informado pelo responsável técnico e confirme o domínio antes de digitar sua senha.
2. Faça login apenas em dispositivos confiáveis e mantenha sistema e navegador atualizados.
3. Utilize uma senha longa e exclusiva. Não repita a senha do e-mail, Asaas, Melhor Envio ou Groq.
4. Em computador compartilhado, encerre a sessão ao terminar. Não marque “lembrar senha” em dispositivos de terceiros.
5. Salve arquivos de trabalho da loja em local autorizado e não divulgue documentos de clientes fora da operação necessária.

## As credenciais de serviços externos são diferentes

| Credencial | Para que serve | Cuidados |
| --- | --- | --- |
| Senha do `/admin` | Acesso ao painel da loja | Não enviar a ninguém por mensagem. |
| API Key do Asaas | Criar ou consultar cobranças, conforme permissões | Tratar como senha financeira. Nunca publicar no site ou em prints. |
| Autorização do Melhor Envio | Cotações, compra/gestão de etiquetas | Não enviar token de acesso por WhatsApp. |
| API Key Groq | Gerar textos com IA | Proteger para evitar uso por terceiros e consumo inesperado. |
| Token/segredo de webhook | Validar comunicação de plataformas | Somente o responsável técnico deve receber, por canal seguro. |

## O que fazer quando alguém deixa a equipe

1. Avise o responsável técnico para **revogar o acesso administrativo** dessa pessoa e revisar sessões em aberto.
2. Revise também o acesso dela diretamente ao Asaas, Melhor Envio e Groq, quando aplicável.
3. Troque segredos que possam ter sido compartilhados fora dos mecanismos de acesso individuais.
4. Verifique operações financeiras recentes, pedidos cancelados e etiquetas geradas se houver suspeita de uso indevido.

## Dados de clientes e comprovantes

O painel pode apresentar nome, telefone, documento, endereço e histórico de compras. Use essas informações somente para atender o pedido e atividades comerciais permitidas. Não copie listas para grupos, planilhas públicas ou contas pessoais. Evite enviar print completo do pedido para resolver dúvidas simples.

## Quando interromper a operação e buscar suporte

- A loja pede para inserir uma chave de pagamento em um site desconhecido.
- Existem cobranças ou etiquetas que ninguém da equipe reconhece.
- Um pedido foi marcado pago sem confirmação no gateway.
- Há suspeita de vazamento de API Key, token ou senha.
- Um ex-colaborador ainda consegue entrar no administrativo.

**Não tente “corrigir” um incidente excluindo registros ou refazendo cobranças.** Preserve evidências e peça verificação antes de executar outra operação financeira.

# 4. Cadastrar e organizar categorias

## Por que cadastrar categorias primeiro?

Toda mercadoria precisa pertencer a uma categoria. Elas ajudam os compradores a navegar pelo catálogo e podem aparecer no menu e na página inicial.

## Criar uma categoria

1. Acesse **[Catálogo → Categorias](/admin/categories)**.
2. Clique em **Nova categoria**.
3. Preencha **Nome** com um termo claro, como “Máquinas de serigrafia” ou “Acessórios”.
4. O **Slug** é a parte da URL. Ao criar, deixe em branco para gerar automaticamente. Evite mudar slugs já publicados, pois links compartilhados podem deixar de funcionar.
5. **Descrição** é opcional: explique que tipo de produto o visitante encontrará ali.
6. Envie uma **imagem de destaque** se quiser que o cartão da categoria tenha foto. Aceitos JPG, PNG e WebP de até 5 MB.
7. Defina **Ordem**: os menores números aparecem primeiro onde o site respeita ordenação.
8. Marque **Ativo** para que a categoria apareça publicamente.
9. Clique em **Criar** ou **Salvar**.

## Alterar, desativar e excluir

- Para corrigir nome, descrição ou imagem, abra a categoria na listagem e edite.
- Se não quiser exibir uma categoria temporariamente, desative-a em vez de excluir.
- Uma categoria com produtos vinculados não deve ser excluída; primeiro decida o destino de cada produto e faça a transferência para outra categoria.
- Ao remover uma imagem da associação, o arquivo físico pode continuar armazenado por segurança; essa operação não necessariamente libera espaço em disco.

## Boas práticas

- Use nomes curtos e compreensíveis: “Tintas base água” em vez de “Todos os tipos de tintas para qualquer aplicação”.
- Evite duplicidades (“Acessórios”, “Acessorio” e “Acessórios em geral”).
- Não crie categorias vazias apenas para preencher o menu.
- Após salvar, abra a listagem pública e confira o filtro/menu.

**Problema comum:** produto não aparece na categoria esperada → abra **Produtos**, confirme categoria, status ativo e disponibilidade.

## Exemplo completo: criar a categoria “Tintas”

Imagine que você vende tintas para serigrafia e precisa organizar os produtos. Faça assim:

1. Abra o menu **Catálogo** e entre em **Categorias**.
2. Clique para adicionar uma categoria.
3. Digite **Tintas** no nome. Na descrição, escreva: “Tintas para diferentes técnicas de impressão serigráfica”.
4. Coloque uma foto que represente a categoria, se tiver. A imagem é opcional.
5. Mantenha **Ativo** marcado. Use **Ordem 1** se quiser que apareça perto do início do menu.
6. Salve. Em seguida, abra **Produtos**, edite uma tinta e selecione **Tintas** no campo Categoria. Salve o produto.
7. Entre na loja pública e veja se o produto aparece no grupo correto.

### Perguntas frequentes

**Posso criar a categoria antes de ter produtos?** Sim. Ela só terá itens quando você vincular os produtos.

**Posso mudar o nome depois?** Sim. Se o endereço (slug) já estiver sendo divulgado, evite alterá-lo.

**A imagem é obrigatória?** Não. A loja pode usar outra apresentação quando não houver imagem.

**O botão Salvar não funciona?** Confira se o nome está preenchido e se há mensagens de validação ao lado dos campos.

# 5. Cadastrar produtos passo a passo

## Cadastro completo — do início ao produto visível

Acesse **[Catálogo → Produtos](/admin/products)** e clique em **Novo produto**. O formulário possui seções organizadas; preencha uma a uma.

### 1. Informações

1. **Nome:** descreva exatamente o que será vendido, por exemplo “Mesa de impressão serigráfica 4 berços”.
2. **Slug:** deixe vazio ao cadastrar para gerar uma URL amigável. Alterar a URL de produto já divulgado pode quebrar links.
3. **SKU:** código interno opcional; se informado, deve ser único. Não reutilize o mesmo SKU em produtos diferentes.
4. **Categoria:** escolha uma das categorias já cadastradas.
5. **Descrição curta:** resumo para ajudar o comprador na decisão.
6. **Descrição completa:** explique finalidade, conteúdo da embalagem, modo de uso, compatibilidades e cuidados. O editor permite títulos, listas, negrito, itálico e links.
7. **SEO:** título e descrição de busca são opcionais; se vazios, a loja usa informações do cadastro.

### 2. Mídia

1. Clique em **Adicionar fotos** e envie JPG, PNG ou WebP, cada uma com até **5 MB**.
2. Na edição, confira a foto principal, a ordem da galeria e remova associações indesejadas.
3. A primeira foto adicionada vira principal; você pode promover outra na edição.
4. Se existir, informe a URL do **vídeo demonstrativo do YouTube**.
5. Prefira imagens com produto nítido e fundo adequado; não use fotos que representem acessórios não incluídos na venda sem esclarecer.

### 3. Comercial

- **Preço de custo:** somente administrativo, nunca mostrado ao comprador. Pode ser zero se você não controla custo neste cadastro.
- **Preço de venda:** obrigatório e maior que zero.
- **Preço promocional:** opcional, também maior que zero e sempre menor que o preço normal.
- Os campos monetários usam **digitação por centavos**. Exemplo: `129990` aparece como **R$ 1.299,90**.

### 4. Disponibilidade e estoque

- **Pronta entrega:** mercadoria disponível para expedição imediata, sujeita ao estoque.
- **Sob encomenda:** comprável conforme as regras da loja, sem baixa/reserva de estoque imediato da mesma forma que pronta entrega.
- **Indisponível:** não é ofertado para compra.
- **Estoque:** quantidade em unidades. Campo vazio representa ausência de controle de estoque, não necessariamente estoque zero.

### 5. Transporte (crítico para frete automático)

Informe **peso embalado em kg** e **altura, largura e comprimento em cm**. Use as medidas de uma unidade vendável já embalada. Por exemplo: peso `1.250` kg? **Não:** para 1 kg e 250 g, informe `1,250` kg conforme o campo numérico. Não confunda gramas com quilos. Confira a prévia antes de salvar.

Sem medidas válidas, o Melhor Envio pode não retornar cotações. Produtos muito grandes podem exceder os limites de determinadas transportadoras; isso não significa falha do site.

### 6. Publicação

- **Ativo:** controla a visibilidade pública.
- **Produto em destaque:** candidato à vitrine de destaques da página inicial.
- **Permitir cotação pelo WhatsApp:** habilita a ação de contato comercial se houver telefone configurado.

Clique em **Salvar**, abra a página pública do produto e teste fotos, preço, variação, carrinho e cálculo de entrega.

## Revisão antes da publicação

- O nome descreve o que de fato será entregue?
- O preço e a promoção foram conferidos?
- As fotos mostram o item correto?
- As variantes, se houver, estão completas?
- O peso e as três dimensões correspondem à embalagem real?
- Há estoque disponível ou modalidade sob encomenda adequada?
- O produto e a categoria estão ativos?

## Usar IA no cadastro

No formulário existe **Assistente de IA** para sugerir nome, resumo, descrição e SEO. A IA não define preço, medidas, estoque ou características técnicas sem base. Consulte **Assistente de IA** neste manual para as etapas detalhadas.

## Exemplo completo: cadastrar uma tinta de serigrafia

Este exemplo é apenas ilustrativo: **substitua os dados pelos dados reais do produto**.

1. Vá até **Produtos → Novo produto**.
2. Nome: “Tinta para serigrafia preta — 900 ml”. Se você vende outras cores e embalagens, avalie usar **variações** em vez de criar várias páginas iguais.
3. Selecione a categoria **Tintas**. Se ela não existir, cadastre-a primeiro em Categorias.
4. Na descrição curta, explique o principal benefício sem prometer resultado que não foi comprovado. Na descrição completa, informe uso, substratos compatíveis, modo de conservação e o que acompanha a compra.
5. Cadastre o preço real, escolha se há promoção e informe estoque se houver controle.
6. Adicione fotos reais do produto. Se tiver várias, organize a principal e a galeria.
7. Pese e meça **a embalagem que você vai enviar** (não apenas a embalagem do fabricante). Preencha o peso em kg e as três medidas em cm.
8. Marque **Ativo** e salve. Abra o produto na loja pública.
9. Faça uma simulação de carrinho e frete com CEP válido. Corrija qualquer detalhe antes de divulgar.

### Se alguma informação estiver faltando

Você pode salvar e terminar o cadastro depois. **Não publique um item para venda sem preço correto, identificação clara e condições reais de entrega.** Se não souber o peso ou dimensões, meça antes de habilitar o frete automático. Não invente informação técnica com IA.

### Alterar um produto já publicado

1. Abra **Produtos** e pesquise pelo nome ou SKU.
2. Clique em editar, modifique o campo necessário e salve.
3. Confira o resultado na loja. Alterar o preço atual **não muda o preço de pedidos já feitos**.
4. Se quiser tirar o item de venda temporariamente, desative **Ativo** ou mude a disponibilidade conforme o caso, em vez de apagar um histórico importante.

# 6. Variações, medidas e estoque

## Quando criar variações?

Cadastre variações quando o comprador precisa escolher uma **combinação vendável**. Exemplos: camiseta `Cor=Preto + Tamanho=M`, tinta `Cor=Azul + Embalagem=900 ml`, tecido `Gramatura=120 + Largura=1,20 m`. Um produto que só existe numa configuração **não precisa** de variações.

## Como cadastrar

1. Abra **[Produtos](/admin/products)** e edite o item.
2. Encontre **Opções e variações** e clique em **Adicionar variação**.
3. No campo de características, descreva a combinação completa daquela unidade vendável: não crie uma linha só “Preto” se para vender também é obrigatório escolher tamanho.
4. Marque **Disponível para venda** quando aquela combinação puder ser adquirida.
5. Se possuir, informe **SKU próprio** para diferenciar a variação.
6. Expanda **Ajustes próprios desta combinação** apenas quando ela tiver preço, promoção, estoque ou embalagem diferentes.
7. Salve o produto e confira no site se o seletor exige a combinação correta.

## Como funciona a herança

| Campo da variação | Em branco | Preenchido |
| --- | --- | --- |
| Preço | Usa preço do produto principal | Usa preço da combinação |
| Estoque | Compartilha o estoque geral do produto | Usa saldo independente da combinação |
| Peso e dimensões | Usa embalagem geral | Usa embalagem própria, com os quatro dados completos |
| Promoção | Pode herdar promoção geral somente quando herda o preço | Promoção específica precisa ser menor que o preço efetivo |

**Exemplo de estoque:** produto tem 10 unidades no estoque geral. Duas cores com estoque vazio **compartilham essas mesmas 10 unidades**; não são 10 para cada cor. Se a cor Azul tem 3 unidades próprias, apenas essa combinação usa o saldo próprio.

## Reservas de estoque

O sistema considera as reservas válidas ao verificar disponibilidade. Reserva **não diminui imediatamente o estoque físico**; o consumo acontece ao confirmar pagamento no fluxo correspondente. Pedido de frete sob consulta pode reservar por 24 horas, segundo a regra do checkout assistido.

## Atenção ao editar variações

- Evite mudar características de uma combinação já usada em pedidos: pedidos guardam cópias das características contratadas.
- Remover uma variação pelo formulário a **desativa**; combinações com reserva não podem ser simplesmente redefinidas.
- Um preço próprio não herda automaticamente o desconto promocional do produto.
- Não cadastre preço/medidas diferentes sem confirmar o custo de expedição.

**Problema comum:** o cliente não consegue adicionar ao carrinho → confira se a combinação escolhida existe, está ativa, o produto está ativo e há disponibilidade.

# 7. Preços e promoções

## O que é uma promoção de produto?

É um **preço promocional cadastrado no próprio produto**. Não confunda com cupom: promoção altera o preço anunciado; cupom é um código aplicado no checkout, com regras específicas.

## Criar uma promoção

1. Abra **[Catálogo → Produtos](/admin/products)** e edite o produto.
2. Na seção **Comercial**, mantenha o **Preço de venda** normal.
3. Informe **Preço promocional** menor que o preço de venda e maior que zero.
4. Salve. O catálogo passa a poder exibir o preço anterior riscado e o valor promocional, conforme o template.
5. Confira o item na vitrine e, quando houver, no filtro/listagem **Promoções**.

**Exemplo:** preço de venda **R$ 250,00** e preço promocional **R$ 199,90**. O valor a cobrar pelo produto será R$ 199,90, antes de cupom e frete, respeitadas as regras comerciais.

## Como retirar a promoção

Edite o produto e **limpe o campo Preço promocional**; não substitua o preço normal por zero. Para destacar produtos sem dar desconto, marque **Produto em destaque**.

## Promoção versus cupom

- O preço promocional vale para todos os compradores que podem adquirir o produto naquele momento.
- Cupom exige inserir um código válido no checkout, respeitando data, limite, categoria/produto e identidade do cliente.
- Por padrão, o cupom pode **não contemplar itens já em promoção**, dependendo do campo **Permitir produtos já em promoção** no cadastro do cupom.
- Não some manualmente descontos; o sistema aplica as regras no backend.

## Como consultar

Na listagem de produtos, utilize o filtro **Em promoção** para localizar os itens com preço promocional válido. Produtos sem oferta válida não entram automaticamente na vitrine de promoções.

# 8. Banners: imagens, formatos e agendamento

## O que é um banner?

É uma imagem de destaque da **página inicial**, normalmente apresentada no carrossel principal. Pode anunciar campanhas, categorias ou produtos. Um banner não cria desconto automaticamente; se anunciar uma promoção, o produto correspondente também precisa ter preço correto.

## Tamanhos e formatos das imagens

O formulário **não obriga uma dimensão exata em pixels**: ele aceita JPG, PNG ou WebP, com **até 5 MB por arquivo**. Entretanto, para evitar cortes e distorções, use estas **recomendações de design** e confira o resultado em cada template:

| Uso | Referência recomendada | Observação |
| --- | --- | --- |
| Desktop | **1600 × 600 px** (proporção 8:3) | É a proporção indicada pelo componente de banner atual; ideal para uma arte horizontal. |
| Celular | **750 × 900 px** como ponto de partida | Não é um requisito técnico; teste no celular real. Se houver cortes, ajuste conforme o template. |
| Formato | WebP ou JPG para fotos; PNG para transparências | Comprima para manter carregamento rápido. |

O site utiliza `object-contain` no banner original, portanto diferenças de proporção podem gerar **espaços vazios**, em vez de cortar a arte. O template alternativo pode ter apresentação diferente. Evite escrever informações muito importantes junto às bordas, botões do carrossel ou áreas com pouco contraste.

## Como cadastrar

1. Vá a **[Configurações → Banners](/admin/banners)** e escolha **Novo banner**.
2. Dê um **Título** interno que permita localizar a campanha mais tarde.
3. Em **Link**, cole a URL completa de destino (`https://...`) se quiser um banner clicável. Teste esse endereço antes de salvar.
4. Preencha **Texto alternativo** descrevendo a imagem para acessibilidade. Exemplo: “Promoção de tintas para serigrafia”.
5. Em **Imagem desktop**, envie a arte horizontal (obrigatória).
6. Em **Imagem mobile**, envie a arte vertical quando quiser uma composição otimizada para celulares (opcional).
7. Marque **Ativo** para permitir a exibição.
8. Defina **Ordem** (menores números têm prioridade).
9. Opcionalmente, preencha **Início** e **Fim** para agendar a campanha. O intervalo inclui as datas/horários limites, e a data final não pode ser anterior à inicial.
10. Salve e confirme a aparência no computador e no celular.

## Campanha agendada: exemplo

- Título: “Semana de ofertas”.
- Imagem desktop: 1600×600 px.
- Imagem mobile: arte adaptada e legível para celular.
- Início: 01/11 às 08:00.
- Fim: 07/11 às 23:59.
- Ativo: sim.

Fora desse intervalo, o banner não será exibido publicamente mesmo que continue no banco.

## Problemas frequentes

- **Não aparece:** veja se está ativo, se a data de início já chegou, se não expirou e se a imagem foi salva.
- **Aparece em branco:** verifique o formato do arquivo, existência do link de armazenamento público e o resultado no navegador.
- **Texto pequeno no celular:** use uma arte mobile própria; não dependa da redução da arte de desktop.
- **Banner abre destino errado:** revise o campo Link e teste a URL.
- **Lentidão:** compacte a imagem; não é necessário usar 10 MB para uma arte de banner (o limite do formulário é 5 MB).

## Exemplo pronto de campanha na página inicial

Imagine um banner que anuncia um produto com desconto durante uma semana.

1. No produto, cadastre e salve o **preço promocional correto**. O banner sozinho não reduz preço.
2. Prepare uma imagem horizontal de **1600 × 600 px** para computador e, se possível, uma arte própria para celular. Essa é uma sugestão de criação, **não uma obrigação do formulário**.
3. Abra **Banners → Novo banner** e preencha o título interno, como “Semana da Serigrafia — novembro”.
4. Na URL do link, cole o endereço da página real do produto ou da categoria.
5. Envie a imagem, marque como ativo e escolha o período da campanha.
6. Salve. Se a data de início já tiver chegado, confira a home. Se for uma campanha futura, confira quando entrar em vigor.

### Antes de divulgar

- Abra o site no celular e no computador: a arte permanece legível?
- Clique no banner: ele leva ao produto ou página anunciada?
- Os preços e o período promocional estão de acordo com o anúncio?
- A imagem contém informações escondidas em bordas ou cortadas?

**Se precisar de uma peça nova:** não edite a arte antiga sem guardar uma cópia do arquivo. O ideal é manter os banners por campanha, ativando e desativando conforme necessário.

# 9. Blog: artigos, categorias e IA

## Antes de publicar: ativar o blog

Em **[Configurações da loja](/admin/store-settings)**, seção **Blog**, marque **Ativar blog no e-commerce**. Só então o visitante verá o menu e as páginas públicas. Se desejar, marque também **Exibir “Últimas do Blog” na página inicial**. Essa segunda opção não ativa o blog sozinha.

## Criar uma postagem

1. Entre em **[Conteúdo → Postagens do blog](/admin/blog-posts)** e clique em **Nova postagem**.
2. Escreva um **Título** claro; por exemplo, “Como escolher uma tinta para serigrafia”.
3. O **Slug** é o endereço da postagem. Deixe vazio ao criar para sugerir um slug a partir do título. Não altere depois de divulgar o link sem avaliar redirecionamentos.
4. Digite o **Conteúdo** no editor visual. Use subtítulos, parágrafos curtos, listas, citações e links; não cole código de scripts.
5. Preencha **Resumo/chamada** para usar na listagem e nos cards.
6. Envie a **Imagem de destaque**; acrescente texto alternativo que descreva a foto.
7. Escolha a **Categoria editorial** existente. Você pode criar uma nova diretamente pelo seletor; ela fica disponível em outros artigos.
8. Nas **Etiquetas**, selecione as já usadas ou crie novas, como “tintas”, “tutoriais” e “novidades”.
9. Preencha opcionalmente **Título SEO** e **Descrição SEO**.
10. Escolha **Rascunho** para guardar sem exibir ou **Publicado** para disponibilizar de acordo com a data.
11. Se quiser publicar depois, configure a data/hora no campo **Publicar em**. Confira o fuso e o horário.
12. Salve e abra o artigo na loja pública.

## Rascunho, publicação e agendamento

- **Rascunho:** visível apenas para quem administra o blog.
- **Publicado com data atual ou anterior:** poderá ficar visível quando o blog estiver habilitado.
- **Publicado com data futura:** aguarda a data/hora prevista para aparecer.
- Desativar o blog oculta a área pública, mas preserva os artigos no admin.

## Categoria e etiqueta: qual a diferença?

- **Categoria editorial** agrupa o conteúdo em um assunto principal, como “Novidades” ou “Dicas de uso”.
- **Etiquetas** descrevem temas adicionais e permitem reutilização, como “plastisol”, “equipamentos” ou “iniciantes”.
- Não precisa redigitar tudo: o cadastro mantém um catálogo de termos reutilizáveis, inclusive quando uma postagem que os utilizava é excluída.

## Criar ou melhorar uma postagem com IA

1. Abra a tela Criar/Editar e acione **Assistente de IA**.
2. Escolha **Escrever nova postagem** para produzir conteúdo a partir de um assunto, ou **Melhorar texto existente** para trabalhar no rascunho.
3. Informe objetivo, público e informações verdadeiras do assunto. Ajuste **tom** e **extensão**.
4. Revise a prévia de título, resumo, texto e SEO antes de aplicar.
5. Aplique somente os campos desejados e clique em **Salvar**. A IA não publica automaticamente, não altera categoria, capa nem estado do artigo.

> Confira números, normas, especificações e promessas de produto. A IA escreve textos, mas **não substitui a conferência humana** de dados técnicos ou comerciais.

## Dicas para bons artigos

- Um título útil, um problema concreto e resposta estruturada.
- Parágrafos claros; subtítulos para facilitar a leitura.
- Fotos com direitos de uso adequados e descrições acessíveis.
- Links internos para produtos ou categorias relevantes.
- Revisão ortográfica e SEO sem repetição artificial de palavras-chave.

## Exemplo de artigo pronto para publicar

Imagine que você queira divulgar dicas de manutenção de equipamentos:

1. Abra **Postagens do blog** e clique em criar.
2. Título: “Como conservar seus equipamentos de serigrafia”.
3. Escolha a categoria editorial, como **Dicas de manutenção**, ou crie uma categoria nova.
4. Adicione etiquetas relevantes, como **Serigrafia** e **Conservação**. Se elas já estiverem cadastradas, selecione-as.
5. Escreva uma introdução, três ou quatro dicas úteis e uma conclusão. Use subtítulos para facilitar a leitura.
6. Adicione uma imagem de destaque, resumo e, se desejar, título e descrição de SEO.
7. Salve como rascunho, abra a prévia administrativa e revise. Depois publique ou agende.
8. Veja a página pública. Se a opção **Últimas do Blog** estiver ativa nas Configurações da loja, a postagem poderá aparecer na home.

**Dica:** o assistente de IA pode criar um primeiro rascunho. Você ainda precisa revisar todas as informações, imagens, referências e direitos de uso.

# 10. SEO, divulgação e campanhas

## O que é SEO no e-commerce?

SEO significa melhorar a clareza e a organização das páginas para mecanismos de busca. Não existe botão que garanta primeiro lugar no Google. Um cadastro de qualidade ajuda clientes e buscadores a entenderem os produtos.

## Publicar um produto com título e descrição úteis

1. Em **[Produtos](/admin/products)**, use nome identificável: tipo, material e modelo, quando conhecidos.
2. Preencha a **Descrição curta** com o benefício principal e a finalidade do item.
3. Na **Descrição completa**, separe informações por subtítulos: aplicação, materiais, características confirmadas, cuidados e conteúdo da embalagem.
4. Não utilize medidas, potência, compatibilidade, prazo ou garantia que não foram conferidos.
5. Informe **Título SEO** e **Descrição SEO** se desejar adaptar o texto exibido na busca. Quando vazios, o sistema usa informações do produto como alternativa.
6. Associe o item à categoria correta, adicione fotos nítidas e salve.
7. Abra o produto no site e confira o resultado em celular.

### Exemplo de preenchimento

| Campo | Exemplo ilustrativo |
| --- | --- |
| Nome | Rodo para Serigrafia 20 cm |
| Descrição curta | Rodo manual para aplicação de tinta em processos de serigrafia. |
| Título SEO | Rodo para Serigrafia 20 cm — Nome da Loja |
| Descrição SEO | Conheça o rodo para serigrafia de 20 cm e confira características, fotos e condições de compra. |

O exemplo é apenas de redação. **Não afirma que um produto específico possui essas características**; confira o item real antes de publicar.

## Blog para dúvidas dos clientes

1. Acesse **[Blog](/admin/blog-posts)** e crie um artigo que responda a uma pergunta real dos clientes.
2. Escreva um título direto; use subtítulos para separar etapas ou assuntos.
3. Preencha resumo, imagem de destaque, categoria editorial e etiquetas reutilizáveis.
4. Revise as informações técnicas, especialmente quando usar o **Assistente de IA**.
5. Configure SEO, escolha rascunho ou publicação e revise o artigo na loja.
6. Para destacar artigos na home, ative **Blog** e **Últimas do Blog** em **[Configurações da loja](/admin/store-settings)**.

### Ideias que ajudam o cliente

- Como escolher um equipamento de acordo com a aplicação.
- Quais medidas conferir antes de comprar uma peça.
- Cuidados de limpeza e conservação, com orientação do fabricante.
- Comparações baseadas em especificações verificadas.
- Perguntas frequentes sobre embalagem, frete e prazo.

## Banners e campanhas

O banner é uma chamada visual. **Ele não modifica preço, estoque ou cupom.** Antes de anunciar “20% OFF”, cadastre a condição comercial verdadeira e teste a página de destino. Use **[Banners](/admin/banners)** para incluir arte desktop e mobile, link, ordem e intervalo da campanha.

## Como verificar se uma campanha funcionou

Confira pedidos aprovados, ticket médio, produtos vendidos e período no Dashboard. Não confunda um aumento de visualizações com pagamentos aprovados; nem atribua automaticamente todas as vendas ao banner. Para análise avançada de tráfego, são necessários instrumentos externos específicos que não fazem parte deste módulo.

## Cuidados ao divulgar

- Não prometa frete grátis, parcelamento ou descontos que o checkout não oferece.
- Não exponha dados pessoais de compradores em provas sociais ou imagens de pedidos.
- Não copie fotos ou textos de terceiros sem autorização.
- Evite termos absolutos como “o melhor do mercado” sem base verificável.

# 11. Cupons: cadastro e regras

## O que é um cupom?

É um código digitado pelo cliente no **checkout** para obter desconto sob regras definidas por você. **Não é a mesma coisa que preço promocional**: o preço promocional é exibido diretamente no produto, enquanto o cupom precisa ser aplicado e validado.

## Cadastrar um cupom

1. Abra **[Vendas → Cupons de desconto](/admin/coupons)** e clique em **Novo cupom**.
2. **Código:** informe algo simples, como `BEMVINDO10`. O sistema normaliza o código e impede duplicidade.
3. **Descrição interna:** explique o objetivo da campanha para você e sua equipe.
4. **Tipo de desconto:** escolha **Percentual** ou **Valor fixo**.
5. Se percentual, informe um inteiro de **1% a 100%**. Se fixo, informe valor monetário em reais; a aplicação armazena centavos.
6. Em **Aplicável a**, escolha **Todos os produtos**, **Categorias selecionadas** ou **Produtos selecionados**. Se selecionar categorias/produtos, indique exatamente quais entram na campanha.
7. Decida se o cupom pode abranger **produtos já em promoção**, usando **Permitir produtos já em promoção**.
8. Opcionalmente, defina **Subtotal mínimo** e **Desconto máximo**. O subtotal considera itens, **sem frete**.
9. Opcionalmente, defina **Limite total de utilizações** e datas **Válido a partir de/até**.
10. Ative **Cupom ativo** e salve.
11. Teste com um pedido de exemplo que satisfaz a regra e com outro que não satisfaz.

## Exemplos práticos

**Campanha de 10% na loja:** `BEMVINDO10`, tipo Percentual, valor 10, abrangência Todos, ativo. Sobre subtotal elegível de R$ 200,00, o desconto será R$ 20,00, salvo limites/regras adicionais.

**R$ 30 de desconto em tintas:** tipo Valor fixo, valor R$ 30,00, abrangência Categorias selecionadas → Tintas, com subtotal mínimo se desejar. Só os produtos elegíveis entram no cálculo; não assuma que o frete será descontado.

**Produto já em promoção:** com a opção **Permitir produtos já em promoção** desligada, itens promocionais não entram automaticamente no desconto daquele cupom.

## Uma utilização por CPF/CNPJ

O sistema controla a aplicação e o consumo por **documento do comprador** (CPF ou CNPJ), inclusive para clientes que compram sem criar conta. Em regra, o mesmo documento não pode aproveitar o mesmo cupom novamente após o uso confirmado. Não tente contornar isso criando outra conta com o mesmo documento.

## Reserva, confirmação e cancelamento

1. No checkout, o cliente aplica o código e o sistema valida vigência, ativação, elegibilidade, valor mínimo, limite total e documento.
2. Ao criar o pedido, o uso pode ficar **reservado** enquanto a cobrança aguarda conclusão.
3. **Após pagamento aprovado**, a utilização é registrada como efetiva.
4. Em caso de cancelamento/expiração **confirmados**, a reserva é tratada conforme as regras do sistema. Não libere manualmente a reserva só porque o comprador fechou o navegador.
5. Se o checkout estiver aguardando confirmação do Asaas, não considere o uso liberado até receber o evento definitivo.

## Se o cupom é recusado, revise

- Código digitado corretamente, cupom ativo e dentro das datas?
- O carrinho possui produto/categoria contemplado?
- Há produtos promocionais bloqueados pelo cupom?
- A compra cumpre o mínimo? O desconto máximo está configurado?
- O cupom ou o documento já atingiu o limite?
- Há checkout pendente que ainda reserva o cupom?

> **Não exclua registros de reserva ou de histórico diretamente no banco para “liberar” um cupom**: isso pode permitir reutilização incorreta e prejudicar a auditoria.

## Exemplo completo: criar `CLIENTE10` com 10% de desconto

1. Abra **Cupons de desconto** e clique em criar.
2. Código: `CLIENTE10`. Descrição interna: “Primeira campanha de boas-vindas”.
3. Tipo: **Percentual**. Percentual: **10**.
4. Em **Aplicável a**, escolha **Todos os produtos**. Se quiser restringir, selecione Categorias ou Produtos e informe quais são válidos.
5. Selecione se o desconto também vale para itens que já estão em promoção.
6. Se quiser, coloque **Subtotal mínimo** (por exemplo, R$ 100) e **Limite de utilizações**. Não preencha limite se não deseja limitar a campanha por quantidade.
7. Escolha a data final ou deixe sem vencimento. Marque **Cupom ativo** e salve.
8. Abra a loja, adicione um produto elegível e teste o código no checkout. Se o subtotal de produtos elegíveis for R$ 200, o desconto de 10% será de R$ 20, respeitando as regras que você configurou.

### O que significa “uso reservado”?

Uma pessoa pode aplicar o cupom e iniciar um pedido **sem ter pago ainda**. O sistema pode reservar aquela utilização para não ultrapassar o limite da campanha. Se a venda terminar com pagamento confirmado, vira utilização efetiva. Se ela for cancelada ou expirar segundo o fluxo, a reserva será liberada conforme a confirmação do sistema.

**Exemplo:** um cupom com limite total de 2 usos pode parecer esgotado com 1 venda paga e 1 checkout ainda pendente. Não aumente o limite nem limpe o banco antes de investigar os pedidos pendentes.

### Dúvidas comuns

**O desconto inclui frete?** Não: as regras consideram os produtos elegíveis, não o valor da entrega.

**O mesmo cliente pode usar a campanha novamente com outro cadastro?** A proteção usa CPF/CNPJ; mudar e-mail não altera a regra.

**Criar cupom altera o preço cadastrado?** Não. O desconto é aplicado ao pedido válido no checkout.

# 12. Clientes, contas e endereços

## Consulta de clientes

Acesse **[Vendas → Clientes](/admin/customers)**. A listagem permite localizar cadastros e abrir o detalhe de uma pessoa para conferir informações e compras associadas.

## Como o cliente cria uma conta

1. Na loja pública, o comprador pode criar cadastro com nome, e-mail, telefone, CPF/CNPJ e senha.
2. A área do cliente é separada do acesso dos funcionários ao administrativo.
3. O comprador pode manter vários endereços, definindo um endereço principal.
4. O CPF/CNPJ permanece protegido contra alteração comum na área da conta.
5. O cliente pode consultar **Meus pedidos**, acompanhar o status e verificar o rastreamento quando disponível.

## Compra sem cadastro

A loja permite **checkout de visitante**. Ele informa nome, e-mail, telefone, documento e endereço para realizar o pedido. Isso **não cria automaticamente uma conta**. A vinculação de um pedido de visitante a uma conta criada depois segue validações da sessão de origem, e-mail e documento. Não vincule manualmente pedidos apenas porque dois clientes informaram o mesmo e-mail.

## Endereços

- O primeiro endereço salvo vira o principal.
- Ao definir outro como principal, o anterior deixa de ser o padrão.
- Excluir o endereço principal promove outro endereço existente.
- Pedidos antigos preservam o endereço usado naquela compra: modificar o cadastro do cliente não altera um pedido já efetuado.

## Data de nascimento

A data de nascimento é opcional no cadastro. Quando preenchida, pode alimentar a área de **Aniversariantes do mês** no dashboard, útil para ações comerciais. Use dados com respeito à privacidade e não exporte documentos ou dados pessoais sem necessidade operacional.

## O que fazer quando um cliente não encontra um pedido?

1. Consulte a lista de pedidos no administrativo pelo número, e-mail ou dado que a tela disponibilizar.
2. Confira se a compra foi feita como visitante ou cliente autenticado.
3. Evite associar manualmente pedidos sem comprovar titularidade.
4. Não compartilhe links privados de pedidos nem capturas com CPF/endereço de outra pessoa.

## Senhas

O painel não deve mostrar senhas dos clientes. O cliente altera a própria senha nos recursos da área de conta, mediante autenticação e validação da senha atual.

# 13. Pedidos e mudanças de status

## Onde acompanhar

Use **[Vendas → Pedidos](/admin/orders)** para ver detalhes, pagamento, valores, itens, endereço, cotação, rastreio e histórico. Use **[Pedidos Kanban](/admin/orders-kanban)** para acompanhar visualmente as etapas operacionais. Não arraste pedidos para status arbitrários: o sistema valida transições permitidas.

## Entenda os status — e quando mudar

| Status exibido | O que significa | Ação usual |
| --- | --- | --- |
| **Pedido recebido** | Pedido criado; pagamento ainda pode estar pendente. | Aguardar pagamento ou cotar o frete sob consulta. |
| **Pedido pago** | Pagamento aprovado/confirmado pela integração. | Clicar **Iniciar preparação**. |
| **Em preparação** | Separação, conferência e embalagem. | Preparar etiqueta/envio ou preparar retirada. |
| **Pronto para envio** | Etiquetas geradas; produto ainda não foi necessariamente postado. | Postar mercadorias; acompanhar confirmação da transportadora. |
| **Enviado** | Remessa postada/confirmada ou envio manual registrado. | Acompanhar transporte até entrega. |
| **Pronto para retirada** | Pedido preparado para o cliente buscar. | Entregar fisicamente e concluir. |
| **Entregue** | Entrega/retirada concluída. | Encerrar acompanhamento. |
| **Cancelado** | Venda encerrada dentro das regras de cancelamento. | Conferir separadamente reembolso e etiqueta, se houver. |

## Fluxo normal de entrega automática

1. O cliente escolhe produto, endereço, frete e finaliza a compra.
2. O gateway processa a cobrança. O retorno do navegador **não basta** para confirmar pagamento: a integração recebe evento do provedor.
3. Quando aprovado, o pedido fica **Pedido pago**.
4. Clique em **Iniciar preparação** no pedido.
5. Confira itens/quantidades, embalagens e dados do remetente.
6. Clique em **Gerar etiqueta** e conclua as etapas do Melhor Envio, quando aplicável.
7. Com etiquetas geradas, o pedido fica **Pronto para envio**. **Imprima, fixe e poste** a encomenda.
8. O status **Enviado** depende da confirmação logística do provedor. Acompanhamento/rastreio atualiza a jornada.
9. A confirmação de entrega conclui o pedido.

## Fluxo de retirada no local

**Pedido recebido → Pedido pago → Em preparação → Pronto para retirada → Entregue**. Retirada não necessita gerar etiqueta nem código de rastreio. Só conclua como entregue depois de efetivamente entregar o item ao comprador.

## Fluxo de envio manual

Na etapa de preparação, a ação **Marcar enviado** solicita transportadora e permite código/link de rastreio quando disponíveis. Utilize-a **somente para serviço manual**, não para simular a confirmação de uma etiqueta automática.

## Cancelar pedido: o que acontece?

- O cancelamento comercial possui confirmação e motivo interno opcional, dentro dos status permitidos.
- **Cancelar pedido não estorna pagamento automaticamente.** Se já pagou, trate o reembolso também junto ao meio de pagamento e acompanhe seu processamento.
- **Cancelar pedido não cancela uma etiqueta automaticamente.** Se já há frete comprado, examine **Cancelar etiqueta** quando permitido.
- Não use cancelamento comum em pedido enviado/entregue; esses são estados terminais ou exigem outro fluxo comercial.

## Conferência do pedido

Abra o detalhe e confira: número, data, dados de contato, documento do comprador, endereço de entrega, produtos, variações, quantidades, preços, cupom/desconto, total, forma de entrega, pagamento, envios e histórico. O pedido registra valores e dados contratados no momento da compra; editar o produto posteriormente não reescreve o pedido antigo.

## Exemplo guiado: um pedido do começo ao fim

**Dia 1 — Cliente comprou e pagou:** abra **Pedidos**, selecione a compra e confira se o status realmente está **Pago**. Um pedido “Recebido” ainda pode estar aguardando confirmação; não envie sem confirmação financeira.

**Dia 1 — Preparação:** clique **Iniciar preparação**, se a ação estiver disponível. Separe os produtos, confira quantidade, variação e embalagem.

**Dia 1 — Etiqueta:** se o frete foi contratado por Melhor Envio, siga o assistente **Gerar etiqueta**. Informe NF-e ou declaração de conteúdo somente quando apropriado. Imprima o documento e fixe corretamente. Se o transporte for manual, siga o fluxo de envio manual. Se for retirada, prepare o pedido para o balcão.

**Dia 2 — Postagem:** entregue o pacote à transportadora ou ponto indicado, guarde o comprovante e acompanhe o rastreio. Não confunda a simples impressão da etiqueta com a postagem.

**Após a entrega:** acompanhe a confirmação do transporte. Se houver atraso, busque informações no serviço de entrega antes de alterar qualquer status.

### Checklist de conferência antes de preparar

- O pagamento está confirmado pelo sistema e, se necessário, pelo Asaas?
- O cliente informou dados de contato e endereço completo?
- Itens, quantidades e variações correspondem ao que foi comprado?
- O desconto e o frete constam do resumo do pedido?
- A embalagem e os documentos de transporte estão corretos?

### Pedido preso em uma etapa

Primeiro abra os detalhes e leia a mensagem de erro/histórico. Depois consulte o tutorial específico de **Pagamentos**, **Etiquetas** ou **Frete sob consulta**. **Nunca mude um status só para esconder uma falha de integração.**

# 14. Pós-venda, devoluções e estornos

## O pós-venda começa antes de marcar um pedido como entregue

Depois de confirmar o pagamento, a equipe deve preparar, conferir, enviar e acompanhar o pedido. **Concluir a venda no painel não garante que o cliente recebeu o produto correto**, por isso mantenha uma rotina de conferência e atendimento.

## Cliente informa que pagou, mas pedido continua recebido

1. Abra o pedido em **[Vendas → Pedidos](/admin/orders)** e confira número, documento, total e data.
2. Consulte a cobrança correspondente na **[configuração de pagamentos](/admin/payment-methods)** e no painel do Asaas.
3. Confirme se o ambiente é o mesmo: Sandbox não equivale a Produção.
4. Se o Asaas indicar pagamento e o SABA não, **não force a mudança de status**. Solicite revisão do webhook pelo responsável técnico.
5. Evite iniciar separação, compra de frete ou envio até esclarecer a aprovação do pagamento.

## Pedido cancelado: o dinheiro foi devolvido?

**Não necessariamente.** O status comercial **Cancelado** indica o encerramento do pedido no SABA. Ele **não é comprovante de estorno** no banco ou no Asaas.

1. Verifique no pedido o status do pagamento e o motivo do cancelamento.
2. Se a cobrança foi paga, consulte no Asaas quais operações de estorno estão disponíveis para o método utilizado.
3. Execute e acompanhe o procedimento financeiro na plataforma apropriada, conforme sua política e legislação aplicável.
4. Registre internamente a data, o valor, o comprovante e o andamento do estorno.
5. Confirme ao cliente somente depois de verificar o resultado real do provedor; prazos de devolução variam por método de pagamento.

> Não faça uma segunda cobrança para “compensar” uma cobrança anterior sem diagnóstico. Não presuma que cancelar o pedido estorna automaticamente o cartão ou Pix.

## Cliente quer cancelar depois que a etiqueta foi comprada

1. Abra o pedido e verifique se existe **etiqueta** associada.
2. Consulte as ações **Verificar envio** e **Cancelar etiqueta**, conforme disponíveis no caso concreto.
3. Se o pacote já foi postado ou a etiqueta está em fase não cancelável, a logística pode exigir outro procedimento com o provedor.
4. Confirme separadamente o cancelamento comercial, a situação do frete e o eventual reembolso do cliente.
5. Guarde protocolos do transportador e do gateway.

**Cancelar pedido, cancelar etiqueta e reembolsar pagamento são três operações diferentes.** Uma não deve ser inferida da outra.

## Produto com defeito, troca ou devolução

Esta versão do SABA **não apresenta um fluxo completo de RMA/devolução e troca com etiqueta reversa automática** no painel. Por isso:

1. Localize o pedido original e confira o item exato, variação e data.
2. Registre o motivo relatado pelo cliente, evidências e condições de recebimento, usando o procedimento interno autorizado.
3. Verifique a política aplicável e os direitos do consumidor; não prometa prazos fora das regras vigentes.
4. Avalie a logística reversa e a disponibilidade de troca com o transportador e o responsável comercial.
5. Se for necessário estorno ou cobrança adicional, trate diretamente no provedor financeiro, sem alterar o valor histórico do pedido.
6. Não marque o pedido original como “cancelado” ou “entregue” apenas para simular um processo de troca.

## Encomenda extraviada ou sem atualização

- Confirme o **código de rastreio** e consulte a transportadora/Melhor Envio.
- Verifique se a encomenda foi realmente **postada**: etiqueta impressa não significa pacote coletado.
- Abra solicitação com a transportadora dentro dos prazos e mantenha o cliente informado.
- Evite gerar outra etiqueta para o mesmo pacote antes de entender o status da primeira.
- Encaminhe decisões comerciais de reenvio ou reembolso ao responsável pela operação.

## Checklist para encerrar o atendimento

- [ ] Pedido e documento do cliente foram confirmados.
- [ ] Status no SABA foi confrontado com gateway e transportadora.
- [ ] Cliente recebeu orientação clara, sem promessa de reembolso ou entrega não confirmados.
- [ ] Operações adicionais foram registradas internamente.
- [ ] Evidências necessárias foram preservadas com acesso restrito.

# 15. Meios de entrega e cotações

## Onde configurar

Abra **[Configurações → Meios de Entrega](/admin/shipping-methods)**. O SABA reúne provedores de frete, retirada e operação manual em uma área específica.

## Antes de ativar frete automático

1. Preencha **Configurações da loja → Frete → CEP de origem**.
2. Confira endereço de remetente, razão social, CNPJ e dados fiscais necessários.
3. Cadastre **peso e dimensões de cada produto e variação**. Peso em kg; altura, largura e comprimento em cm.
4. Crie e verifique sua conta no Melhor Envio.
5. Conecte a conta ao SABA pelo fluxo OAuth descrito no tutorial **Criar conta e conectar o Melhor Envio**.

## Configurar Melhor Envio

1. Em **Meios de Entrega**, localize o card **Melhor Envio** e clique em **Configurar**.
2. A configuração possui áreas/abas para **Conexão**, **Transportadoras e serviços**, **Unidades de postagem** e **Operação**.
3. Em **Conexão**, use ambiente correto (Sandbox ou Produção), credenciais correspondentes e fluxo de autorização da sua conta.
4. Em **Transportadoras e serviços**, **sincronize** o catálogo com o provedor e habilite somente os serviços que realmente deseja oferecer.
5. Serviços novos começam **desativados** e em operação manual; **compra automática** só deve ser habilitada após homologação do serviço na conta.
6. Em **Unidades de postagem**, atualize as agências por filtros de transportadora e localização; escolha a unidade de preferência quando aplicável.
7. Confira configurações operacionais e salve cada aba conforme as instruções do formulário.
8. Ative a integração no card e faça cotações de teste com produtos/CEPs reais e embalagens válidas.

## Por que o checkout pode não oferecer entrega?

- O produto não possui peso e medidas suficientes.
- A transportadora não atende o CEP informado.
- O volume ultrapassa os limites do serviço.
- O serviço está desativado, indisponível ou não foi sincronizado.
- A integração ficou desconectada ou o ambiente está incorreto.

O sistema **não inventa** frete grátis nem medidas. Se não existir opção automática, pode oferecer **Frete sob consulta**, caso permitido pelo fluxo.

## Retirada na loja

No card **Retirada**, clique **Configurar** e preencha **Nome exibido, Endereço da retirada, Instruções e Prazo de preparação**. Salve e ative a opção. A retirada aparece como alternativa independente de etiqueta, quando elegível.

## Outros provedores

Os cards de **SuperFrete** e **Correios direto** podem aparecer como opções futuras. **Isso não significa integração direta funcional**: os Correios apresentados pelo Melhor Envio fazem parte da integração Melhor Envio. Não prometa serviços que ainda não foram implementados/homologados.

## Regras importantes

- A cotação de entrega pertence ao pedido efetuado; gerar etiqueta depois **não deve recalcular silenciosamente o preço contratado**.
- Ao cancelar etiqueta, você não cancela automaticamente a venda.
- Quando houver vários volumes, podem existir várias etiquetas/slots para o mesmo pedido.

# 16. Gerar, imprimir e postar etiquetas

## O que é uma etiqueta de envio?

É o documento gerado pela transportadora para identificar e encaminhar a encomenda. **Gerar etiqueta**, **comprar frete**, **imprimir**, **postar** e **entregar** são etapas distintas. Uma etiqueta impressa não significa que a transportadora já recebeu o pacote.

## Antes de clicar em “Gerar etiqueta”

1. Abra **[Vendas → Pedidos](/admin/orders)** e selecione o pedido.
2. Confirme **pagamento aprovado** e status **Em preparação**. Se o pedido está apenas “Recebido”, verifique o pagamento ou a cotação.
3. Separe itens e confira **quantidade, variação, tamanho, peso e embalagem real**.
4. Verifique endereço, CEP, destinatário e dados de remetente (incluindo CNPJ/inscrição estadual quando exigidos).
5. Confira o método de frete contratado, transportadora e serviço. Nem todos os serviços usam as mesmas regras de agência, seguro e documentos.
6. Tenha o documento de transporte adequado à operação: **NF-e** ou, quando legalmente cabível, **Declaração de conteúdo**.

## Gerar etiqueta no painel

1. Com pedido pago, clique **Iniciar preparação**.
2. No pedido **Em preparação** com frete automático, clique em **Gerar etiqueta**.
3. Revise o quadro **Entrega contratada**: serviço, transportadora, volumes e valor pago pelo cliente.
4. Selecione **Tipo de documento**: **Nota Fiscal (NF-e)** ou **Declaração de conteúdo**.
5. Para **NF-e**, informe a chave **real** de 44 dígitos da NF-e **modelo 55** emitida externamente. Uma chave inventada, mesmo com tamanho e dígito corretos, pode ser recusada pelo provedor.
6. Para **Declaração de conteúdo**, use a opção **somente quando a legislação e a transportadora permitirem**. O sistema compõe itens, quantidades e valores a partir do pedido. Se você já tiver uma DC-e externa, pode informar sua chave opcionalmente.
7. Se aparecer **Unidade preferida de postagem**, escolha uma unidade elegível. Ela só é obrigatória quando a regra do serviço exigir.
8. Confirme o envio. O sistema inicia a criação/compra e a geração de etiquetas de acordo com as capacidades do serviço; **uma confirmação pode envolver mais de uma etiqueta**.
9. Abra a etiqueta pela ação **Abrir / imprimir etiqueta**. Na produção, podem aparecer formatos adicionais para baixar, como PDF, ZPL e JPEG, conforme o provedor.
10. Fixe a etiqueta correta na embalagem correspondente e **poste a encomenda** no ponto/transportadora indicado. Guarde comprovante de postagem.

## O sistema emite nota fiscal?

**Não.** O SABA solicita os dados necessários para o Melhor Envio, mas **não emite NF-e**. Emita o documento fiscal aplicável em seu emissor/contabilidade e só depois informe a chave no painel. Em caso de dúvida sobre possibilidade de declaração de conteúdo, consulte seu contador e as exigências do serviço.

## Vários volumes

Um único pedido pode ter diferentes envios/volumes. Verifique os links e status de **todos** os volumes. Não considere o pedido pronto se ainda faltar etiqueta de algum volume.

## E após gerar?

- **Etiqueta gerada:** pronta para imprimir/postar.
- **Pronto para envio:** a expedição está preparada; ainda não é confirmação de postagem.
- **Enviado:** o provedor confirmou postagem, ou houve fluxo manual/contingência válido.
- **Entregue:** existe confirmação de entrega.

## Se algo der errado

Não clique repetidamente em **Gerar etiqueta** diante de resultado incerto. Vá para o tutorial **Ações da etiqueta e solução de falhas**: a ação **Verificar envio** existe justamente para evitar compras duplicadas.

## Exemplo simples: a embalagem já está pronta, e agora?

1. Confirme que o pedido está **Pago** e já passou para **Em preparação**.
2. Confira se o cliente escolheu uma entrega automática que permite emissão pelo SABA.
3. Gere a etiqueta seguindo os campos do painel. Use **NF-e** se houver nota fiscal emitida e a operação exigir; use **declaração de conteúdo** somente se legalmente permitida.
4. Se a etiqueta aparecer como disponível, **imprima**, cole na embalagem e confira se código de barras e endereços estão legíveis.
5. Leve a encomenda ao local de postagem indicado pelo serviço. Guarde o recibo físico/digital.
6. Confira se o acompanhamento atualizou. Se estiver parado, consulte o histórico e a ação **Atualizar status**; não marque entregue sem confirmação.

### Diferença entre cada etapa

- **Cotação:** descobrir preço e prazo de entrega; não compra frete.
- **Compra/geração de etiqueta:** contrata o serviço de transporte, conforme o provedor.
- **Impressão:** transforma o documento em papel/etiqueta; não comprova postagem.
- **Postagem:** transportadora recebe o pacote.
- **Entrega:** destinatário recebe o pacote.

Não é possível pular etapas só porque o comprador tem urgência.

# 17. Ações da etiqueta e solução de falhas

## Entenda os botões relacionados a uma etiqueta

No detalhe de **[Pedidos](/admin/orders)** as ações aparecem conforme status, provedor e condição do envio. Nem todo botão estará disponível o tempo todo.

### Gerar etiqueta

Prepara e solicita uma etiqueta com base no **pedido pago e no frete contratado**. Requer os dados de transporte e documento aplicável. Se houver vários volumes, podem existir operações distintas por volume.

### Abrir / imprimir etiqueta

Abre a etiqueta gerada em outra aba. Use para imprimir e fixar no pacote correto. Caso a impressão falhe no Sandbox, não conclua que a compra ou geração falhou; verifique o status antes de repetir operações.

### Baixar arquivo

Na operação de produção, o painel pode oferecer **PDF, ZPL ou JPEG** conforme suporte do serviço e disponibilidade da etiqueta. ZPL costuma ser utilizado em impressoras térmicas compatíveis. O botão não aparece para todo provedor/ambiente.

### Atualizar rastreamento / Atualizar status da etiqueta

Consulta o provedor para buscar novas informações do transporte ou de uma geração ainda aguardando conclusão. **Não realiza nova compra de frete**. Se não houver mudança, aguarde nova informação do operador logístico ou use canal de suporte da transportadora.

### Verificar envio

Aparece quando uma operação de criação/compra ficou em **estado incerto** (por exemplo, falha de conexão após a solicitação). O sistema consulta o Melhor Envio para tentar descobrir se uma etiqueta já foi criada ou comprada e, quando seguro, prosseguir com a mesma operação.

**Como usar:** abra o pedido → localize o volume com alerta → clique **Verificar envio** → confirme → aguarde o resultado. Se continuar pendente, **não force uma segunda compra**: investigue com suporte técnico ou provedor.

### Cancelar etiqueta

Cancela **o envio logístico** quando o Melhor Envio permitir. O painel solicita motivo e confirmação. **Não cancela o pedido comercial nem estorna automaticamente o pagamento do cliente.** Também não restitui estoque do produto por si só. Consulte as regras/custos do provedor.

### Preparar novo envio

Após uma tentativa cancelada, essa ação prepara uma **nova tentativa** no mesmo slot de expedição. Use quando a venda ainda precisa ser enviada e o cancelamento anterior foi confirmado. Ao continuar, poderá haver nova compra de frete. Não use enquanto há operação incerta sem verificação.

### Contingência: Enviado / Entregue

Ação excepcional para casos em que o transporte **não consegue confirmar automaticamente** o status, mas você possui evidência operacional suficiente. Exige **justificativa** e gera trilha de auditoria. Não serve para “adiantar” status nem mascarar falta de postagem.

## Problemas comuns e comportamento correto

| Situação | O que fazer | O que não fazer |
| --- | --- | --- |
| Erro 422 ao montar etiqueta | Leia a mensagem, corrija endereço, documento, dimensões ou serviço | Inventar dados fiscais ou repetir sem corrigir |
| Operação ficou incerta | Use **Verificar envio** | Comprar outra etiqueta por tentativa |
| Etiqueta já paga, mas sem PDF | Use **Atualizar status** e **Abrir / imprimir**; confira limitações de formato | Concluir que nada foi cobrado |
| Cliente cancelou a compra | Verifique separadamente cancelamento comercial, reembolso e cancelamento de etiqueta | Supor que uma ação faz as três |
| Etiqueta cancelada e pedido ainda precisa sair | **Preparar novo envio** após cancelamento confirmado | Apagar envio antigo do banco |
| Correios/Jadlog sem unidade esperada | Sincronize agências/transportadoras, confira serviço e obrigatoriedade | Escolher agência incompatível ou enviar ID aleatório |

## Não confunda os três cancelamentos

1. **Cancelar pedido**: encerra a venda quando permitido.
2. **Cancelar etiqueta**: tenta cancelar operação logística específica.
3. **Reembolsar cobrança**: envolve meio de pagamento e confirmação financeira separada.

Antes de refazer uma operação, consulte o histórico do pedido e do envio.

# 18. Frete sob consulta e negociação

## Quando aparece “Frete sob consulta”?

Quando o sistema não encontra uma forma automática elegível para entregar em um CEP válido, pode permitir que o comprador **solicite uma cotação negociada**. Isso também pode ocorrer quando a loja só oferece retirada local. Não é frete grátis e **não significa que o pedido foi pago**.

## O que o cliente vê

1. O cliente preenche identidade, endereço e itens do pedido.
2. Sem entrega automática disponível, seleciona solicitação de **frete sob consulta**.
3. O pedido é registrado como **Pedido recebido**, com pagamento pendente.
4. O valor de frete e o total aparecem como **A definir** até sua proposta.
5. Se houver controle de estoque, pode haver reserva por **24 horas**. Abrir o link novamente não prorroga esse prazo.

## O que a loja deve fazer

1. Abra **[Vendas → Pedidos](/admin/orders)** e localize pedidos recebidos aguardando cotação.
2. Abra o detalhe e clique **Informar cotação de entrega**.
3. Preencha **Transportadora**, **Serviço/descrição**, **Valor do frete**, **Prazo estimado em dias úteis** (se conhecido), **Instruções** e **Válida até**.
4. Salve a proposta. Ela cria uma revisão; publicar nova cotação substitui a proposta anterior.
5. Use **Abrir link do cliente** para visualizar o link protegido e enviá-lo ao comprador pelo canal adequado. O sistema não envia WhatsApp automaticamente só porque você publicou.
6. O cliente abre, revisa valores finais e confirma a cotação. O fluxo de pagamento é iniciado somente quando ele escolhe concluir.
7. Após confirmação do pagamento pelo meio configurado, prepare o pedido e cuide do envio manual conforme o serviço negociado.

## O que não pode mudar durante a cotação

Os itens, quantidades, preços e o endereço do pedido já possuem **snapshot**: não são recalculados com base em cadastros atualizados após a solicitação. O frete e o total só ficam definidos quando a proposta é publicada e confirmada.

## Segurança do link

- O link contém proteção e validade. Não publique em redes sociais nem repasse a terceiros.
- Comprador cadastrado deve se autenticar como titular; visitante usa o link protegido.
- Visualizar a página (GET) **não aceita a cotação nem cobra** automaticamente.
- Uma proposta vencida ou substituída precisa de nova confirmação; o sistema revalida estoque após expiração da reserva.

## Erros mais frequentes

- **Valor final continua “A definir”:** cotação ainda não foi publicada/confirmada.
- **Link expirou:** publique nova proposta, com novo prazo.
- **Cliente não consegue confirmar:** confira titularidade, validade, revisão e disponibilidade de estoque.
- **Pedido apareceu sem pagamento:** normal até que o cliente aceite e conclua o pagamento.

# 19. Pagamentos Asaas e cobranças

## Como a loja recebe pagamentos

A integração operacional configurável do projeto é o **Asaas**, com **checkout hospedado** e pagamento por **Pix** e/ou **cartão de crédito**, conforme opções ativadas e disponibilidade da conta. O comprador é encaminhado ao fluxo do gateway. O SABA acompanha o resultado por **webhooks validados**, e não apenas pelo retorno da página do navegador.

## Configurar no administrativo

1. Abra **[Configurações → Meios de Pagamento](/admin/payment-methods)**.
2. Escolha **Sandbox** para testes ou **Produção** para vendas reais. As contas e chaves são independentes.
3. Informe **API Key** correspondente ao ambiente.
4. Informe **Token de autenticação dos Webhooks**, com pelo menos 32 caracteres, **idêntico** ao cadastrado no webhook no Asaas.
5. Ajuste **Validade do checkout** (entre 10 e 1440 minutos) e **Parcelamento máximo** (1 a 21, sujeito às regras comerciais do Asaas).
6. Escolha habilitar **Pix**, **Cartão de crédito** ou ambos.
7. Salve e clique **Testar conexão salva**.
8. Configure no Asaas a URL exibida pelo SABA e os eventos listados no quadro do formulário.
9. Só então marque **Ativar Asaas no checkout**, salve e faça um teste ponta a ponta.

## Eventos Asaas esperados pelo sistema

O quadro do administrativo informa a URL exata do webhook, além dos eventos **CHECKOUT_CREATED**, **CHECKOUT_PAID**, **CHECKOUT_CANCELED** e **CHECKOUT_EXPIRED**. Configure **todos os eventos indicados** e o mesmo token nos dois sistemas.

## Como verificar o pagamento de um pedido

1. Abra **[Pedidos](/admin/orders)** e encontre a compra.
2. Veja o status operacional e o **status do pagamento**, que são conceitos separados.
3. Consulte o histórico do pedido e dados de pagamentos.
4. Se o comprador afirma que pagou, mas o pedido ainda consta recebido, confira a transação na conta Asaas e a entrega/validação do webhook.
5. Não altere manualmente o status para Pago sem confirmação autoritativa do gateway.

## Cancelamento e revisão

Em **Meios de Pagamento → Últimas cobranças Asaas** aparecem cobranças e sinalizações como **Requer revisão**. Para cobranças pendentes elegíveis existe a ação **Solicitar cancelamento**.

> **Solicitar cancelamento não significa cancelar instantaneamente nem liberar estoque/cupom.** O sistema aguarda confirmação definitiva do Asaas. **Cancelar um pedido não é automaticamente reembolso**. O estorno/reembolso, quando aplicável, deve ser tratado como procedimento financeiro próprio e confirmado no Asaas.

## O que ainda não é equivalente a pagamento real?

- **Mercado Pago** aparece como desativado para novas compras, com registros/código anteriores preservados.
- **Efí** não está implementado nesta versão.
- Não confunda checkout simulado ou testes Sandbox com cobrança efetiva de produção.

## Teste mínimo antes de ativar cobranças reais

Valide pagamento aprovado, pagamento cancelado, expiração de checkout, duplicidade de notificação, recuperação após erro e bloqueio de pedidos sem pagamento. Não salve credenciais em notas públicas nem as envie por mensagens sem proteção.

# 20. Criar conta e configurar o Asaas

## 1. Criar sua conta Asaas

O **Asaas** é o provedor que recebe as cobranças do checkout desta loja. Para cadastrar-se, acesse o site oficial: **[asaas.com](https://www.asaas.com/)**. Utilize seus dados reais de empresa ou pessoa conforme o tipo de conta e conclua a verificação solicitada pelo provedor.

- Para desenvolvimento e testes, o Asaas possui um ambiente separado: **[sandbox.asaas.com](https://sandbox.asaas.com/)**.
- **Sandbox e Produção são contas independentes**, com dados, chaves e URLs distintas. Nunca use a chave de teste em produção.
- O próprio Asaas decide requisitos de validação, taxas, limites e habilitação de métodos de cobrança. Confirme esses dados diretamente no painel da sua conta.

## 2. Gerar uma API Key

1. Entre no painel web do Asaas **no ambiente correto**.
2. Abra a área **Integrações → Chaves de API** (os nomes da interface podem sofrer alterações).
3. Crie uma chave nova, com identificação que permita reconhecer seu e-commerce.
4. **Copie e guarde em local seguro imediatamente**: segundo a documentação oficial, a chave é exibida uma única vez.
5. Não coloque a chave no frontend, código-fonte público, planilhas compartilhadas, prints ou mensagens em grupos.

Referência oficial: [Como criar e gerir chaves de API no Asaas](https://docs.asaas.com/docs/chaves-de-api).

## 3. Configurar no SABA

1. Abra **[Configurações → Meios de Pagamento](/admin/payment-methods)**.
2. Escolha **Sandbox** ou **Produção**.
3. Cole a **API Key** no campo correspondente.
4. Defina um token longo e secreto para autenticação de webhooks (pelo menos **32 caracteres**); ele deve ser igual na configuração do Asaas e do SABA.
5. Defina Pix, cartão, validade do checkout e parcelamento conforme sua operação.
6. Clique **Salvar** e depois **Testar conexão salva**.

## 4. Configurar webhook

1. Na tela de Meios de Pagamento do SABA, copie a **URL do webhook Asaas exibida pelo painel**. Não invente uma URL.
2. No painel do Asaas, procure configuração de **Webhooks** e cadastre a URL.
3. Configure os eventos **CHECKOUT_CREATED, CHECKOUT_PAID, CHECKOUT_CANCELED e CHECKOUT_EXPIRED** indicados pelo SABA.
4. Configure o **mesmo token de autenticação** definido no SABA.
5. Salve, realize compra no Sandbox e confirme se o pedido muda de **Recebido** para **Pago** após notificação válida.

Referências: [documentação inicial do Asaas](https://docs.asaas.com/docs/visao-geral), [ambiente Sandbox](https://docs.asaas.com/docs/sandbox) e [API Keys](https://docs.asaas.com/docs/chaves-de-api).

## 5. Passar de testes para produção

- Crie/use conta **real** de produção, com aprovação e cadastros exigidos pelo Asaas.
- Gere **nova chave de produção** e configure novo webhook para o ambiente real.
- No SABA, troque o seletor para **Produção** e informe as credenciais **desse** ambiente.
- Teste uma cobrança real controlada, incluindo confirmação do webhook, antes de anunciar a loja como operacional.

## Erros comuns

- **401/sem conexão:** chave inválida ou ambiente incorreto.
- **Cobrança aprovada no Asaas, pedido pendente:** webhook não chegou, URL ou token divergente, ou evento ainda não processado.
- **Cancelamento não liberou reserva:** aguarde confirmação terminal autenticada; investigue no painel Asaas e com suporte técnico.

**Importante:** este roteiro ensina a **criar e conectar sua conta**, não concede acesso ao dinheiro da empresa nem determina taxas cobradas pelo provedor.

## Roteiro de verificação para quem nunca configurou pagamentos

1. **Conta:** entre no site oficial Asaas, cadastre-se e conclua as verificações exigidas para receber pagamentos.
2. **Ambiente:** decida se você está testando (Sandbox) ou recebendo cobranças reais (Produção). Mantenha as chaves separadas.
3. **Chave:** crie uma chave de API no painel do ambiente escolhido e copie-a para **Meios de Pagamento** no SABA. Não cole em um campo de endereço ou em conversas públicas.
4. **Webhook:** copie a URL mostrada pelo SABA, cadastre no Asaas com o mesmo token secreto e ative os eventos necessários. Essa etapa é essencial para que a loja saiba que o pagamento foi confirmado.
5. **Teste:** faça uma compra com valor de teste. Abra o pedido em SABA e confirme que ele mudou de **Recebido** para **Pago** somente depois do evento válido.
6. **Conferência final:** a cobrança aparece no painel do Asaas e o pedido correspondente no SABA? Se não, revise ambiente, URL, token e eventos antes de ativar produção.

**Pergunta comum — “O cliente voltou para o site; o pedido já está pago?”** Não necessariamente. O retorno do navegador, sozinho, não comprova o pagamento. Verifique a confirmação registrada pelo provedor.

**Pergunta comum — “O SABA devolve automaticamente o dinheiro se eu cancelar?”** Não. Consulte o procedimento de pós-venda e execute o estorno no meio de pagamento aplicável quando necessário.

# 21. Criar conta e conectar o Melhor Envio

## 1. Abrir uma conta no Melhor Envio

O **Melhor Envio** calcula e intermedeia serviços de transportadoras e a geração das etiquetas. Acesse o site oficial **[melhorenvio.com.br](https://melhorenvio.com.br/)** e escolha **Cadastrar**.

1. Preencha dados de identificação, contato e senha solicitados pelo Melhor Envio.
2. Complete o cadastro do titular e os dados que o provedor exigir.
3. Siga o processo de **verificação de conta**: é possível que o painel peça confirmação de identidade, foto e documento. Sem verificação, a emissão de etiquetas pode ficar bloqueada.
4. Faça login, confira seus dados e leia as regras de postagem, seguros e limites das transportadoras.

Referências oficiais: [Como se cadastrar e entrar](https://centraldeajuda.melhorenvio.com.br/hc/pt-br/articles/31220393418516-Como-se-cadastrar-e-fazer-login-na-plataforma-do-Melhor-Envio) e [Como verificar a conta](https://centraldeajuda.melhorenvio.com.br/hc/pt-br/articles/31220416677780-Como-fa%C3%A7o-a-verifica%C3%A7%C3%A3o-da-minha-conta-no-Melhor-Envio).

## 2. Preparar o SABA

Antes de conectar:

- Em **[Configurações da loja](/admin/store-settings)** informe o endereço e **CEP de origem**, dados fiscais e contato do remetente.
- Em **[Produtos](/admin/products)** preencha peso e três dimensões de cada produto/variação.
- Tenha à mão o endereço público da loja e a **URL exata de callback** informada pela configuração do Melhor Envio no SABA.

## 3. Cadastrar o aplicativo de integração (etapa técnica/administrativa)

1. No painel do Melhor Envio, entre em **Integrações → Área Dev.**
2. Clique em **Cadastrar aplicativo**.
3. Preencha nome, site da plataforma, e-mails de contato e descrição conforme solicitado.
4. Informe a **URL de redirecionamento (callback)** exatamente igual à indicada no SABA. Qualquer diferença pode causar erro `Client invalid`.
5. A documentação do projeto recomenda deixar **desmarcada** a opção para alterar transportadoras e serviços durante a instalação; esses itens serão administrados no SABA.
6. Após salvar, tenha acesso ao **Client ID** e **Client Secret**. Não compartilhe o segredo ou o salve em locais públicos.

Referências oficiais: [Criando um aplicativo](https://docs.melhorenvio.com.br/docs/criando-um-novo-aplicativo) e [Fluxo de autorização](https://docs.melhorenvio.com.br/reference/fluxo-de-autoriza%C3%A7%C3%A3o).

> Dependendo do tipo de integração e das permissões de sua conta, a criação do aplicativo pode ser feita pelo responsável técnico. O lojista pode realizar a autorização da própria conta depois que o aplicativo estiver configurado.

## 4. Conectar pelo SABA (OAuth)

1. Entre em **[Configurações → Meios de Entrega](/admin/shipping-methods)**.
2. No card **Melhor Envio**, clique **Configurar** e abra a aba **Conexão**.
3. Selecione o ambiente adequado e configure os dados do aplicativo exigidos pelo formulário.
4. Use a ação de **conectar/autorizar** para abrir o Melhor Envio.
5. No portal do Melhor Envio, autentique-se com a sua conta e confirme as permissões necessárias.
6. Aguarde o retorno ao SABA e verifique o estado de conexão.
7. Não cole manualmente tokens de acesso em telas públicas nem envie códigos de autenticação por mensagens.

## 5. Selecionar transportadoras e serviços

1. Abra a aba **Transportadoras e serviços** da configuração.
2. Clique em **Sincronizar transportadoras** e aguarde atualização.
3. Ative **apenas** os serviços que deseja oferecer aos clientes.
4. Confira o modo de operação: novos serviços começam desativados e/ou manuais. A compra automática requer testes e homologação específicos.
5. Abra **Unidades de postagem**, atualize por cidade/UF/transportadora e configure a unidade compatível quando o serviço precisar.
6. Revise as opções de **Operação**, salve e, no card principal, ative a integração.
7. Simule frete com CEPs e volumes conhecidos para validar preços, limites e disponibilidade.

## 6. Como é a compra de uma etiqueta?

A venda é primeiro aprovada no SABA. Depois, no pedido, o operador entra em **Em preparação**, escolhe **Gerar etiqueta**, preenche NF-e/declaração aplicável, confirma a compra do frete, imprime e posta. **Não tente comprar etiqueta de pedido ainda não pago**.

## 7. Custos, crédito e problemas

- A conta do Melhor Envio pode exigir **saldo ou meio de pagamento** para comprar frete: verifique as condições na própria conta.
- Etiquetas em Sandbox têm limitações e não representam necessariamente a operação comercial real.
- Se a compra ficar incerta, use **Verificar envio** no SABA antes de tentar novamente.
- Serviços e agências têm regras próprias; não invente identificadores ou medidas para contornar rejeições.

Referências: [Autenticação e conexão](https://docs.melhorenvio.com.br/docs/autenticacao), [Como gerar um envio](https://centraldeajuda.melhorenvio.com.br/hc/pt-br/articles/31220440109844-Qual-o-passo-a-passo-para-gerar-um-envio).

## Roteiro para testar a integração antes da primeira venda

1. Cadastre-se no **Melhor Envio** e finalize a confirmação de identidade e dados de contato solicitados.
2. Confira no SABA o **CEP de origem** da loja e o endereço completo do remetente.
3. Cadastre um produto com peso e três dimensões reais. Sem essas informações, o frete automático pode não ser calculado.
4. Configure ou autorize o aplicativo Melhor Envio na área de **Meios de Entrega**. Se o sistema pedir Client ID, Client Secret e callback, use exatamente os dados do aplicativo criado no painel do provedor.
5. Sincronize transportadoras e selecione os serviços desejados; confirme se a integração aparece conectada e ativa.
6. Acesse a loja como cliente e faça uma simulação para um CEP atendido. Confira valor, prazo e transportadora.
7. Faça uma venda de teste no ambiente apropriado, acompanhe o pedido pago e experimente a geração de etiqueta conforme as regras da plataforma.

### Se a cotação não aparecer

Verifique na ordem: CEP de origem, CEP de destino, peso, altura, largura, comprimento, situação da conexão, serviços habilitados e limites de tamanho/peso de cada transportadora. Não corrija o problema com medidas fictícias: uma transportadora pode recusar ou cobrar diferença depois.

### Se o Melhor Envio disser que a compra da etiqueta falhou

Confira a mensagem e **Verificar envio** no pedido. Em caso de resultado incerto, é possível que a compra já tenha ocorrido. Repetir a operação pode gerar cobrança duplicada.

# 22. Assistente de IA: Groq para produtos e blog

## Qual inteligência artificial o SABA usa?

O provedor integrado é o **Groq**, com **q** no final (**GroqCloud**). Ele não é o **Grok**, produto diferente. O SABA usa a API do Groq para ajudar a redigir informações de produtos e postagens do blog. O modelo configurável pode ser `openai/gpt-oss-120b` ou `openai/gpt-oss-20b`, conforme as opções que aparecem no painel.

A IA **sugere conteúdo**; você decide o que aplicar. Ela não publica ou altera valores comerciais automaticamente.

## Criar conta no Groq e obter API Key

1. Abra o portal oficial **[console.groq.com](https://console.groq.com/)**.
2. Crie sua conta, confirme o acesso e entre no console.
3. Acesse **[API Keys](https://console.groq.com/keys)**.
4. Gere uma chave para a sua loja, identifique-a corretamente e copie para um local protegido.
5. Confira os limites de uso e eventuais custos do plano no painel da sua conta. A existência de camada gratuita não garante capacidade ilimitada.

Referência oficial: [Guia inicial do Groq](https://console.groq.com/docs/quickstart).

## Configurar no SABA

1. Abra **[Configurações → Inteligência Artificial](/admin/ai-settings)**.
2. Ative **Assistente de IA**.
3. Selecione **Groq** como provedor e escolha o modelo oferecido pelo formulário.
4. Cole sua **API Key**. Após salvar, ela ficará oculta: o painel não deve exibir o segredo novamente.
5. Opcionalmente, defina **Limite mensal interno de gerações** para controlar consumo no SABA. Ele **não substitui os limites do Groq**.
6. Preencha **Instruções da loja** com preferências de tom, público e restrições de comunicação. Exemplo: “Use português claro, evite promessas não comprovadas e não invente especificações”.
7. Clique **Salvar e testar conexão**.
8. Confira os indicadores de uso da IA: gerações, tokens de entrada e saída e falhas.

## Como usar IA para cadastrar um produto

1. Entre em **[Produtos → Criar/Editar](/admin/products)**.
2. Comece pelo que você sabe: nome, função, material, medidas reais, uso, compatibilidades e restrições.
3. Abra **Assistente de IA** e escolha criar texto novo ou aprimorar o cadastro.
4. Escreva instruções específicas, como “Melhore a descrição deste rodo de serigrafia para iniciantes, preservando somente as características informadas”.
5. Confira a resposta sugerida campo por campo: **nome, descrição curta, descrição completa, SEO e categoria sugerida**.
6. Aplique apenas os campos aprovados. A IA modifica o formulário, mas **é necessário clicar Salvar** para persistir.
7. Verifique dados técnicos manualmente antes de publicar.

## Como usar IA em postagens do blog

1. Abra **[Postagens do blog](/admin/blog-posts)** e escolha Criar/Editar.
2. Clique **Assistente de IA**.
3. Escolha **Escrever nova postagem** ou **Melhorar texto existente**.
4. Informe assunto, objetivo, público, informações confirmadas, tom e tamanho do texto.
5. Revise título, resumo, conteúdo formatado e SEO. Aplique somente o que deseja.
6. Ajuste imagem, categoria e etiquetas. **Salvar** é necessário; a publicação depende do status e da data configurados no artigo.

## O que a IA não pode decidir por você

- **Preço de venda, custo e promoção**.
- **Estoque, SKU, peso e dimensões**.
- Características técnicas não fornecidas, compatibilidades não verificadas ou garantias inexistentes.
- Política comercial, direitos autorais, instruções de segurança, requisitos legais ou financeiros.

> **Atenção:** um texto convincente pode estar factualmente errado. Confira sempre antes de aplicar ou publicar, principalmente números e especificações técnicas.

## Se a IA falhar

- **Chave inválida:** gere ou salve novamente a API Key correta.
- **Limite excedido:** consulte contagem mensal no SABA e cotas na plataforma Groq.
- **Texto incompleto ou incoerente:** forneça mais dados, reduza o escopo e peça revisão; não invente atributos para completar campos.
- **Falha de conexão:** use **Salvar e testar conexão**, confira status do provedor e tente novamente após estabilização.

## Exemplo de pedido para a IA — produto

No cadastro de uma máquina de serigrafia, você pode escrever:

> “Melhore a descrição para compradores iniciantes. As características confirmadas são: quatro berços, acionamento manual e estrutura de aço. Não invente capacidade, peso ou medidas. Escreva em português simples, com um resumo curto e uma descrição detalhada.”

Depois confira a prévia. Se a IA disser que a máquina pesa 50 kg sem você ter informado isso, **apague a informação inventada**. Aprove apenas os textos corretos.

## Exemplo de pedido para a IA — blog

> “Crie um artigo para iniciantes com dicas de conservação de quadros de serigrafia. Organize com introdução, subtítulos e conclusão. Não afirme que existe uma técnica universal. Use um tom educativo e convide o leitor a conhecer os produtos da loja sem prometer resultados.”

Você pode aplicar **somente o título**, **somente a descrição** ou **todo o texto**, conforme as opções da tela. O conteúdo não será publicado sem salvar e definir o estado correto da postagem.

## Como economizar gerações e obter textos melhores

- Reúna as informações reais antes de abrir o assistente. Um pedido específico costuma exigir menos retrabalho do que “faça uma descrição bonita”.
- Se o resultado estiver muito longo, peça para resumir em vez de gerar novamente sem orientação.
- Compare cada sugestão com a ficha real do produto e com as regras da sua marca.
- Nunca inclua CPF, cartão, senha, chave de API ou dados pessoais de clientes no texto enviado à IA.

# 23. Dashboard, relatórios e aniversariantes

## Como ler o Dashboard

O painel inicial consolida informações comerciais e operacionais da loja. Não é um extrato bancário nem um demonstrativo contábil completo.

### Indicadores comuns

- **Total de pedidos:** quantidade de compras registradas no período aplicável.
- **Faturamento pago:** indicadores calculados a partir de pedidos/pagamentos aprovados; isso não equivale ao saldo disponível no banco.
- **Ticket médio:** valor médio dos pedidos segundo o critério usado pelo widget.
- **Pedidos que exigem ação:** compras que ainda precisam de tratamento operacional, como pagamentos confirmados aguardando preparação.
- **Gráfico por status:** distribuição do andamento dos pedidos.
- **Entregues versus cancelados:** comparação de desfechos operacionais em seus respectivos períodos.
- **Produtos mais vendidos:** itens mais vendidos conforme pedidos aprovados no recorte considerado.
- **Aniversariantes do mês:** clientes que preencheram a data de nascimento, útil para ações de relacionamento.

## Rotina diária recomendada

1. Entre no painel e veja se existem **pedidos que exigem ação**.
2. Abra **[Pedidos Kanban](/admin/orders-kanban)** para visualizar as etapas operacionais.
3. Priorize pedidos **pagos** que ainda não foram para **preparação**.
4. Confira remessas com etiquetas pendentes, operações em verificação e entregas ainda sem confirmação.
5. Examine pagamentos **Requer revisão** em **[Meios de Pagamento](/admin/payment-methods)**.
6. Verifique mensagens de clientes e pedidos de **frete sob consulta** aguardando proposta.
7. Confira itens com baixo estoque e produtos desativados que precisem de atualização.
8. Avalie as métricas semanalmente, não apenas diariamente, evitando tirar conclusões de amostras pequenas.

## Como usar gráficos e valores corretamente

**Venda**, **cobrança aprovada**, **pedido enviado**, **lucro** e **dinheiro disponível na conta** são conceitos diferentes. O painel não conhece automaticamente suas despesas, impostos, custos totais, adiantamentos e extratos de liquidação. Consulte o banco, gateway e sua contabilidade para decisões financeiras.

## Aniversários e privacidade

Não é obrigatório o cliente informar sua data de nascimento. Se esse campo estiver vazio, ele não aparecerá no widget de aniversariantes. Utilize essas informações apenas para finalidades comerciais legítimas e sem expor dados pessoais em canais públicos.

# 24. Dúvidas frequentes e solução de problemas

## Antes de procurar suporte: diagnóstico rápido

### Produto cadastrado não aparece na loja

1. Confira o produto **Ativo**.
2. Confira a **categoria** ativa e correta.
3. Confirme que o produto não está **Indisponível**.
4. Abra a URL do produto diretamente ou faça uma busca pelo nome/SKU.
5. Caso esteja em promoção, confirme preço promocional válido (maior que zero e inferior ao normal).

### Foto não aparece

- Confira extensão JPG, PNG ou WebP e tamanho de até 5 MB.
- Verifique se a associação de imagem foi salva e se existe foto principal.
- Se várias imagens de toda a loja não aparecem, o armazenamento público pode precisar de ajuste técnico (`storage:link`, permissões ou servidor web). Não mova imagens manualmente para lugares desconhecidos.

### Banner não aparece ou parece “cortado”

- Confira **Ativo**, início/fim e link de mídia.
- Refaça arte de desktop em proporção horizontal recomendada (referência 1600×600), e use imagem mobile adaptada.
- O sistema não exige medidas exatas; altere a composição da arte quando a proporção não corresponder à área de exibição.

### Frete retorna indisponível

1. Confira **CEP de origem** da loja e CEP de destino.
2. Confira **peso em kg** e as três dimensões em cm.
3. Verifique se o serviço/transportadora está habilitado e atendendo a região.
4. Se o item for muito grande, veja restrições de tamanho e peso.
5. Use frete sob consulta, quando oferecido, sem inventar valor de frete nem medidas.

### Cupom não funciona

- Confira código, datas, ativo, limite e subtotal mínimo.
- Veja se a categoria/produto está contemplado e se itens promocionais são permitidos.
- Verifique se o CPF/CNPJ já usou o cupom ou possui reserva pendente.
- Não altere tabelas de cupons diretamente para liberar uso.

### Pagamento consta pendente, mas cliente pagou

1. Consulte a cobrança no painel **Asaas**.
2. Confira **Meios de Pagamento → Últimas cobranças** e o status do pedido.
3. Confira se a URL e token de webhook estão corretos, além do ambiente Sandbox/Produção.
4. **Não marque como pago manualmente** apenas com foto de comprovante: solicite diagnóstico técnico da confirmação.

### Etiqueta não foi gerada

- Confira pedido pago, modo de frete, documento fiscal/de conteúdo, chave de NF-e e medidas.
- Rejeição 422 exige correção dos dados indicados.
- Compra de resultado incerto exige **Verificar envio** antes de qualquer nova tentativa.
- Em Sandbox, algumas impressões/formatos podem não estar disponíveis.

### IA apresenta erro

- Faça **Salvar e testar conexão** na página de IA.
- Confira chave, modelo, ativação e limite mensal interno.
- Se a resposta estiver ruim, forneça dados técnicos melhores e revise manualmente.

### Blog ou seção “Últimas do Blog” não aparece

- Em **Configurações da loja → Blog**, ative o blog.
- Ative separadamente **Exibir “Últimas do Blog”**.
- Confirme que existem artigos com status publicado e data válida.

### Trocar o visual do template

O administrador **não troca templates**. Ele altera logo e cores em Configurações da loja. Para mudar o template inteiro, fale com a equipe técnica da instalação.

## Quando a situação exige suporte técnico

- Erros HTTP 500, páginas em branco, arquivos internos expostos.
- Cobrança/estorno duplicado, webhook suspeito ou pedido marcado pago incorretamente.
- Resultado incerto de compra de etiqueta ainda não resolvido por **Verificar envio**.
- Inconsistência de estoque, documento fiscal incorreto ou violação de autorização de dados.
- Ambiente de testes ligado em loja que deveria cobrar de verdade.

Guarde número do pedido, data/hora, tela da operação e mensagem de erro **sem expor chaves, senhas, tokens ou documentos completos**.

# 25. Situações práticas: do cadastro à entrega

## Cenário 1: cadastrar o primeiro produto e vender

**Situação:** você recebeu um produto novo e quer colocá-lo à venda.

1. Acesse **[Categorias](/admin/categories)** e verifique se existe uma categoria adequada. Crie uma se necessário.
2. Em **[Produtos](/admin/products)**, informe nome, categoria, descrição, fotos, preço de custo, preço de venda e disponibilidade.
3. Informe **peso da embalagem e três dimensões reais** quando oferecer frete por transportadora. Confira estoque e variações, se aplicáveis.
4. Marque o produto como ativo e salve.
5. Abra a loja pública pelo botão **Ver loja** e confirme imagem, preço, disponibilidade, escolha de opções e carrinho.
6. Faça uma compra de teste com CEP válido e acompanhe o pedido no administrativo.
7. Não faça envio real com cobrança ou etiqueta de Sandbox. Separe os ambientes de teste e produção.

**Confira:** o produto aparece na categoria e na busca; o checkout mostra valor correto; o pedido apresenta o item e o frete contratados.

## Cenário 2: criar uma promoção com código

**Situação:** você quer dar 10% de desconto para um grupo de produtos.

1. Acesse **[Cupons](/admin/coupons)** e clique em Novo cupom.
2. Crie um código como `OUTONO10`, escolha **Percentual 10** e abrangência **Produtos selecionados** ou **Categorias selecionadas**.
3. Decida se os produtos já promocionais podem receber esse desconto.
4. Defina datas, limite de usos e subtotal mínimo, se necessários; ative e salve.
5. Confira em um carrinho elegível e em outro que não deve receber desconto.
6. Lembre-se: o cupom pode ficar **reservado** durante uma cobrança pendente, e a utilização só é efetivada no fluxo de aprovação.

**Não faça:** excluir reservas no banco, inventar cupom válido em material publicitário ou concluir que o cliente “já usou” apenas porque fechou a aba.

## Cenário 3: pedido pago com frete do Melhor Envio

1. No pedido, confirme **Pedido pago**; não se baseie apenas no retorno de checkout do cliente.
2. Selecione **Iniciar preparação** e separe os produtos.
3. Confira embalagem, documento fiscal aplicável e dados do destinatário/remetente.
4. Use **Gerar etiqueta**; escolha NF-e ou declaração de conteúdo somente quando cabível, e revise todos os envios.
5. Se houver operação pendente ou resultado incerto, use **Verificar envio** antes de repetir qualquer compra.
6. Abra e imprima as etiquetas, fixe-as nos volumes certos e efetue a postagem.
7. Acompanhe o rastreio até entrega. **Pronto para envio** e **Enviado** são etapas diferentes.

## Cenário 4: o cliente pede retirada na loja

1. Configure **Retirada na loja** em **[Meios de Entrega](/admin/shipping-methods)**, incluindo endereço e instruções.
2. Confirme no pedido a modalidade contratada e o pagamento aprovado.
3. Clique **Iniciar preparação**, depois **Pronto para retirada** quando estiver disponível fisicamente.
4. Oriente o comprador sobre local e identificação necessária.
5. Marque **Entregue** somente após realizar a entrega do produto.

**Não faça:** gerar etiqueta de transportadora para retirada, nem marcar entregue antes da retirada ocorrer.

## Cenário 5: o frete exige cotação manual

1. O cliente conclui a solicitação sem frete automático e o pedido fica aguardando proposta.
2. No detalhe do pedido, clique **Informar cotação de entrega**.
3. Cadastre transportadora, serviço, valor, prazo e validade; confirme todos os dados com o transportador.
4. Utilize **Abrir link do cliente** para conferir a proposta publicada.
5. Aguarde o comprador aceitar a condição e concluir o pagamento antes de preparar a mercadoria.
6. Após aprovação, siga a etapa de preparação e envio correspondente.

## Cenário 6: um artigo foi escrito com IA e não aparece na home

1. Abra **[Blog](/admin/blog-posts)** e confira se a postagem foi salva, se está publicada e se a data de publicação já chegou.
2. Em **[Configurações da loja](/admin/store-settings)**, confirme **Ativar blog**.
3. Ative **Exibir “Últimas do Blog”** para mostrar artigos na home. Ela só aparece se existir conteúdo publicado elegível.
4. A IA apenas sugere e preenche campos selecionados; ela **não publica automaticamente**.

## Cenário 7: pagamento confirmado no Asaas, mas pedido pendente

1. Confira se o pedido e a cobrança realmente correspondem ao mesmo comprador, valor e ambiente.
2. Consulte o status de pagamento no Asaas e em **[Meios de Pagamento](/admin/payment-methods)**.
3. Verifique se existem mensagens de falha ou eventos pendentes na integração.
4. Não marque pedido pago manualmente sem identificar a causa. Acione suporte técnico para verificar webhook e processamento de eventos.

## Onde buscar mais detalhes

Use o botão **Ajuda desta tela** no topo do administrativo. Para informações adicionais, consulte **Pedidos**, **Cupons**, **Etiquetas**, **Frete sob consulta**, **Pagamentos** e **Problemas frequentes** no menu desta central.

# 26. Glossário dos termos do e-commerce

## Termos de cadastro e vitrine

**Categoria:** grupo utilizado para organizar produtos (ex.: tintas ou acessórios).

**SKU:** código interno que você utiliza para identificar um produto ou uma variação.

**Slug:** parte amigável do endereço de uma página, geralmente formada pelo nome sem espaços e acentos.

**Produto ativo:** cadastro habilitado para aparecer no catálogo, sujeito também às regras de disponibilidade e categoria.

**Preço promocional:** preço menor que o preço regular, definido diretamente no cadastro. Não depende de código de cupom.

**Variação:** opção ou combinação escolhida pelo comprador (ex.: tamanho M, cor azul). Pode possuir seus próprios valores ou herdar os dados do produto.

**Banner:** arte visual de campanha. Pode ter link e agendamento, mas não cria desconto.

**SEO:** informações e organização de conteúdo que ajudam mecanismos de busca a interpretar uma página. Não garantem posição nas pesquisas.

## Termos de pagamento e pedidos

**Checkout:** etapa em que o cliente confere compra, dados, frete e inicia pagamento.

**Pedido recebido:** pedido criado, mas ainda sem aprovação de pagamento (ou aguardando cotação em alguns fluxos).

**Pedido pago:** compra com pagamento reconhecido pelo sistema, normalmente após confirmação pela integração.

**Sandbox:** ambiente de testes com credenciais e transações separadas de produção. Não representa dinheiro ou postagem real.

**Webhook:** comunicação automática de um provedor (como Asaas) avisando o SABA sobre eventos. O retorno visual do cliente ao site não substitui a confirmação do pagamento.

**Cupom reservado:** limite de uso temporariamente comprometido em um pedido/checkout pendente.

**Cupom consumido:** utilização contabilizada após confirmação do pagamento, respeitando as regras do cupom.

**Estorno/reembolso:** devolução financeira processada pelo provedor de pagamento. Não equivale a cancelar o registro comercial do pedido.

## Termos de entrega

**CEP de origem:** endereço de saída usado para cálculo de frete.

**Frete sob consulta:** proposta de transporte preparada manualmente quando não há cotação automática adequada.

**Etiqueta:** documento emitido pelo serviço de transporte. Pode existir antes de o pacote ser postado.

**Código de rastreio:** identificador do transporte, quando fornecido pela transportadora.

**NF-e:** nota fiscal eletrônica emitida por sistema fiscal apropriado, não pelo módulo de etiquetas do SABA.

**Declaração de conteúdo:** documento que somente pode substituir o documento fiscal quando a legislação e as regras do transporte permitirem.

**Logística reversa:** retorno de um produto ao vendedor. Esta versão do SABA não possui um fluxo completo de troca/devolução automatizado.

## Termos de IA

**Groq:** plataforma que disponibiliza modelos de IA pela API usada no SABA. Não confundir com **Grok**.

**API Key:** segredo que autoriza uso de um serviço. Nunca deve ser divulgado.

**Token de IA:** unidade usada pelos modelos para medir parte do texto processado e gerado; não é o mesmo que uma geração completa.

**Rascunho:** conteúdo salvo no administrativo, ainda não publicado no blog.

# 27. Checklists de abertura e operação

## Checklist: antes de abrir a loja ao público

- [ ] Nome comercial, razão social, CNPJ e contato conferidos.
- [ ] Logo e cores revisados nos dois tamanhos de tela.
- [ ] WhatsApp abre o número correto.
- [ ] CEP de origem do frete e endereço do remetente corretos.
- [ ] Categorias ativas e produtos vinculados corretamente.
- [ ] Produtos com foto, preço, disponibilidade, peso e dimensões completos.
- [ ] Estoque e variações conferidos.
- [ ] Banners legíveis no desktop e celular; links testados.
- [ ] Meio de entrega configurado, testado e habilitado.
- [ ] Asaas em ambiente **correto**, com webhook testado e Pix/cartão conforme desejado.
- [ ] Compra de teste completa e status conferidos até a etapa esperada.
- [ ] Políticas comerciais/contato conferidos com os responsáveis pela operação.

## Checklist: cadastrar um novo produto

- [ ] Categoria correta.
- [ ] Nome, descrição e dados técnicos verdadeiros.
- [ ] Preço de venda maior que zero; promoção inferior ao preço normal, se houver.
- [ ] Estoque/variações corretos.
- [ ] Fotos nítidas e vídeo opcional verificado.
- [ ] Peso em kg e altura/largura/comprimento em cm, da unidade embalada.
- [ ] Produto ativo e disponibilidade certa.
- [ ] Visualização pública e carrinho testados.

## Checklist: início do dia de trabalho

- [ ] Consultar painel e pedidos que exigem ação.
- [ ] Confirmar pagamentos e revisar cobranças pendentes no Asaas.
- [ ] Iniciar preparação de pedidos pagos.
- [ ] Conferir itens, embalagens e prazo de expedição.
- [ ] Publicar propostas de frete sob consulta e comunicar os clientes.
- [ ] Verificar etiquetas em falha ou operação incerta.
- [ ] Imprimir etiquetas geradas, postar encomendas e guardar comprovantes.
- [ ] Acompanhar rastreamento e resolver ocorrências de entrega.

## Checklist: antes de gerar cada etiqueta

- [ ] Pedido pago, em preparação e frete contratado verificado.
- [ ] Endereço/CEP e dados do remetente conferidos.
- [ ] Embalagem real e número de volumes conferidos.
- [ ] Documento fiscal válido ou declaração permitida à operação.
- [ ] Agência exigida pelo serviço selecionada corretamente.
- [ ] Nenhuma operação de compra em estado incerto aguardando verificação.
- [ ] Após geração, imprimir e conferir todas as etiquetas antes de postar.

## Checklist: criar uma campanha promocional

- [ ] Preço promocional/cupom válido e financeiramente sustentável.
- [ ] Itens contemplados definidos (loja inteira, categorias ou produtos).
- [ ] Se for cupom: mínimo, máximo, prazo e limite de uso conferidos.
- [ ] Se desejar alcançar produtos em promoção, configurar essa permissão explicitamente.
- [ ] Banner com arte adaptada, link correto e agendamento.
- [ ] Teste no checkout com CPF/CNPJ de teste que satisfaça os requisitos.

## Checklist: encerramento semanal

- [ ] Verificar pedidos pagos não enviados.
- [ ] Revisar casos de etiqueta cancelada ou compra incerta.
- [ ] Revisar cobrança em análise e reembolsos pendentes no provedor.
- [ ] Atualizar preços/estoque de produtos desatualizados.
- [ ] Revisar cupons vencidos e campanhas encerradas.
- [ ] Conferir indicadores de pedidos e entregas; **não tratar faturamento como saldo bancário**.

## Regra de segurança

Nunca compartilhe **API Key do Asaas, Groq ou Client Secret do Melhor Envio**, senhas, tokens, dados completos de cartão ou documentos pessoais em grupos. Em situações de cobrança ou etiqueta incerta, consulte o provedor e a equipe técnica antes de repetir uma ação irreversível.


---

# Atualização: Usuários, grupos de acesso e segurança

## Quem pode usar o administrativo?

O endereço `/admin` é exclusivo para a equipe da loja. Cada pessoa deve ter seu próprio e-mail e sua própria senha. **Nunca compartilhe contas ou credenciais de serviços externos.**

O painel agora possui **grupos de acesso**. O Administrador decide quais módulos cada pessoa pode abrir e utilizar. As permissões são verificadas também quando alguém tenta acessar uma URL diretamente: esconder o menu não é a única proteção.

## Conheça os dois grupos que vêm prontos

| Grupo | O que pode fazer | O que não pode fazer |
| --- | --- | --- |
| **Administrador** | Acessar e operar todos os módulos, cadastrar usuários e criar grupos | Não há restrições de módulos |
| **Operacional** | Cadastrar categorias, produtos, banners e artigos; gerenciar cupons, clientes, pedidos, etiquetas e financeiro | Não pode abrir Configurações da Loja, Meios de Pagamento, Meios de Entrega, Inteligência Artificial nem Usuários e Grupos |

**Atenção:** o grupo Operacional pode **gerar, cancelar, verificar e imprimir etiquetas dentro de Pedidos**, conforme as regras do envio. Ele **não** pode configurar o Melhor Envio ou mudar credenciais. Também pode usar a IA já configurada nos formulários de produto e blog, mas não mudar a chave Groq.

Os dois grupos padrão são protegidos: não é possível editá-los ou excluí-los pela interface. Assim, as regras acima não mudam por acidente.

## Como cadastrar um novo usuário da equipe

Apenas Administradores ou grupos personalizados autorizados a gerir acessos podem fazer isso.

1. Entre no administrativo com sua própria conta.
2. No menu, abra **Acessos → Usuários administrativos**.
3. Clique em **Novo usuário**.
4. Preencha **Nome**, **E-mail** e **Senha** (use uma senha exclusiva e com pelo menos 10 caracteres).
5. Em **Grupo de acesso**, selecione **Operacional** para um colaborador da operação ou outro grupo adequado.
6. Clique para salvar. Informe ao colaborador o endereço do painel; compartilhe a senha inicial somente por canal seguro.
7. Peça à pessoa que entre e teste as telas necessárias ao trabalho.

**Não escolha Administrador apenas para resolver um problema de acesso.** Esse grupo permite alterar credenciais financeiras e administrar todas as contas do painel.

## Como alterar grupo, nome, e-mail ou senha de um funcionário

1. Abra **Acessos → Usuários administrativos**.
2. Selecione o usuário desejado.
3. Altere o nome, e-mail ou grupo. Para manter a senha atual, **deixe o campo Senha vazio**.
4. Para trocar a senha, digite a nova senha e salve.
5. Peça ao funcionário para entrar novamente e confirmar que as telas corretas aparecem.

O sistema impede que você retire **seu próprio** grupo Administrador e também protege o **último Administrador** da instalação contra rebaixamento. A exclusão de usuários pelo painel está desabilitada nesta versão; quando alguém sair da equipe, atribua a pessoa a um grupo personalizado **sem permissões** para bloquear o acesso e preservar o histórico de operações.

## Como criar um grupo personalizado

Utilize grupos personalizados quando precisar de alguém com acesso limitado, por exemplo, somente catálogo e blog.

1. Vá até **Acessos → Grupos de acesso**.
2. Clique em **Novo grupo**.
3. Informe um nome descritivo, como `Marketing`.
4. Preencha um identificador simples sem espaços, como `marketing`.
5. Em **Permissões por módulo**, marque as áreas necessárias: por exemplo **Banners** e **Blog**.
6. Salve o grupo.
7. Abra o usuário e selecione esse novo grupo no campo **Grupo de acesso**.
8. Faça o teste com o usuário para verificar se ele vê apenas os módulos corretos.

As permissões de um grupo personalizado controlam o **acesso completo a cada módulo** (visualizar, cadastrar e editar, quando o módulo permitir). Não são permissões separadas por botão ou operação. O Dashboard e a Ajuda continuam acessíveis a qualquer conta com grupo válido.

## O que fazer quando alguém sai da equipe

1. Um administrador deve criar (uma única vez) um grupo `Sem acesso` **sem marcar nenhuma permissão** e transferir para esse grupo a conta do colaborador que saiu.
2. Revise também os acessos da pessoa no Asaas, Melhor Envio e Groq, se ela tiver contas externas.
3. Troque credenciais eventualmente compartilhadas e solicite que a pessoa encerre sessões abertas. A mudança de grupo restringe os módulos nas próximas requisições; não substitui a revogação de chaves externas.
4. Caso exista suspeita de uso indevido, preserve registros e procure suporte técnico.

## Cuidados com Asaas, Melhor Envio e Groq

| Credencial | Para que serve | Cuidados |
| --- | --- | --- |
| Senha do `/admin` | Entrar no painel | Individual e nunca compartilhada |
| Chave Asaas | Gerenciar cobranças e pagamentos | Somente pessoas autorizadas a configurações financeiras |
| Token Melhor Envio | Cotações e logística | Não divulgar em mensagens ou imagens |
| Chave Groq | Gerações por IA | Proteja contra consumo indevido |
| Segredo de webhook | Autenticar notificações das plataformas | Uso técnico, nunca exibido publicamente |

## Perguntas frequentes

**Um Operacional pode imprimir uma etiqueta?** Sim, dentro do pedido. Não precisa entrar em Configurações → Meios de Entrega.

**O Operacional pode usar o botão de IA no produto?** Sim, se a IA já estiver habilitada e configurada por um Administrador. Não pode mudar o modelo, chave ou limite da IA.

**Quero um funcionário apenas para cadastrar produtos.** Crie um grupo personalizado que tenha apenas **Catálogo — Produtos**. Se ele precisar criar categorias, libere também **Catálogo — Categorias**.

**Quero permitir acesso ao financeiro, mas não às contas de pagamento.** Libere **Financeiro — Fluxo de caixa** e não libere **Configurações — Meios de pagamento**.

**Não consigo abrir uma página.** Confirme com um Administrador se o seu grupo inclui aquele módulo. Se a mensagem for **403 — Acesso negado**, não tente alterar a URL manualmente.

**Criei o primeiro usuário pelo terminal e não consigo entrar.** O responsável técnico deve conceder o grupo Administrador usando o comando `php artisan saba:grant-admin usuario@exemplo.com`. Esse comando deve ser executado exclusivamente no servidor por quem possui acesso técnico.
