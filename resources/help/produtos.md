## Como cadastrar um produto na loja

Este tutorial mostra o cadastro **do começo ao fim**, inclusive as **variações simplificadas**. Não importa o que sua loja vende: equipamentos, cosméticos, alimentos, roupas, peças, ferramentas ou outro tipo de produto. Os nomes das opções são definidos por você.

Para começar, abra **[Catálogo → Produtos](/admin/products)** e clique em **Novo produto**. Você pode voltar a editar depois; o importante é conferir todos os dados antes de disponibilizar a venda.

## Antes de começar: o que separar

Tenha em mãos o nome, a categoria, fotos, preço real, disponibilidade, quantidade em estoque (se houver controle), peso e medidas **do pacote pronto para envio**. Se o cliente precisa escolher alguma opção, anote os valores possíveis. Por exemplo: Voltagem 110 V ou 220 V, Volume 500 ml ou 1 L, Modelo A ou B.

**Dica:** se a categoria ainda não existe, crie primeiro em **[Catálogo → Categorias](/admin/categories)**. O produto precisa estar vinculado a uma categoria.

## Passo 1 — Informações do produto

Na seção **Informações**, preencha:

1. **Nome:** escreva o que o comprador receberá. Exemplo: “Furadeira de impacto 650 W”. Evite títulos vagos como “Produto novo”.
2. **Slug:** é a parte do endereço da página do produto. Pode ficar vazio na criação; o sistema gera um endereço a partir do nome. Evite alterar o slug depois de divulgar o link.
3. **SKU:** código interno de identificação, opcional. Se usado, precisa ser diferente do SKU de outros produtos. Exemplo: `FUR-650-001`.
4. **Categoria:** selecione uma categoria cadastrada. O campo permite pesquisar pelo nome.
5. **Descrição curta:** explique em poucas frases o que é o produto e seu principal uso.
6. **Descrição:** detalhe materiais, medidas do produto, o que acompanha a embalagem, modo de uso, compatibilidade e cuidados. Use os botões do editor para **títulos, negrito, listas e links**.

**Atenção:** especificações técnicas devem ser verdadeiras. Não diga que um produto é bivolt se o fabricante não informa isso. O editor aceita formatação, mas não scripts nem anexos inseridos no texto.

### Exemplo de descrição

**Nome:** Garrafa térmica 1 litro.

**Descrição curta:** Garrafa térmica para armazenar bebidas quentes ou frias, com capacidade de 1 litro.

**Descrição completa:** Material, instruções de limpeza, dimensões do produto, prazo de garantia e conteúdo da embalagem — **somente se essas informações estiverem confirmadas**.

## Passo 2 — SEO (opcional)

SEO ajuda os buscadores a entenderem sua página.

- **Título SEO:** título exibido, quando aplicável, nos resultados de busca. Procure ser claro; cerca de 60 caracteres é uma referência, não uma obrigação.
- **Descrição SEO:** resumo da página para os buscadores. Cerca de 160 caracteres costuma funcionar bem.

Se deixar esses campos vazios, o site utiliza informações do próprio cadastro. Não é necessário preencher SEO para concluir o produto.

## Passo 3 — Fotos e vídeo

Na seção **Mídia**:

1. Clique em **Adicionar fotos**.
2. Selecione uma ou mais imagens em **JPG, PNG ou WebP**, com até **5 MB por foto**.
3. Salve o produto para adicionar as imagens à galeria. A primeira foto enviada vira a principal inicialmente.
4. Quando estiver **editando** um produto salvo, encontre a **Galeria de fotos** para usar **Definir principal**, **Alterar ordem** ou excluir uma foto da galeria.
5. Se existir um vídeo demonstrativo, coloque a URL do **YouTube** em **Vídeo demonstrativo do YouTube**. É opcional.

Use fotos reais e legíveis. Se mostrar acessórios que não acompanham o produto, deixe isso claro na descrição. Para fotos aparecerem publicamente, o armazenamento público da instalação precisa estar corretamente configurado.

## Passo 4 — Preços e promoção

Na seção **Comercial**, informe:

| Campo | O que significa | Exemplo |
| --- | --- | --- |
| Preço de custo | Quanto custa à loja; uso interno, não mostrado ao comprador. É obrigatório preencher, podendo ser zero se não há custo controlado. | R$ 70,00 |
| Preço de venda | Valor normal cobrado do cliente. Deve ser maior que zero. | R$ 120,00 |
| Preço promocional | Opcional. **Deixe vazio ou 0,00 para não ter promoção.** Se houver oferta, ela deve ser menor que o preço de venda. | R$ 99,90 |

Os campos de dinheiro utilizam entrada em **centavos**: ao digitar `9990`, confira se aparece **R$ 99,90**. Antes de salvar, confira a formatação na tela.

**Para retirar uma promoção:** apague o valor do campo Preço promocional ou informe `0,00` e clique em Salvar. O produto será vendido pelo preço normal. Isso também funciona no campo promocional próprio de cada combinação.

**Promoção não é cupom:** um preço promocional aparece no anúncio do produto; um cupom é um código aplicado no checkout e tem regras próprias.

## Passo 5 — Variações do produto (quando o cliente precisa escolher)

Este é o fluxo simplificado do cadastro, semelhante à ideia de opções e combinações da Shopee. **Não está limitado a Cor ou Tamanho.** Você define livremente os nomes das características e os valores.

**Quando usar?** Use variações quando o mesmo produto é vendido com escolhas diferentes, como **Voltagem**, **Capacidade**, **Fragrância**, **Material**, **Modelo**, **Acabamento**, **Peso da opção**, **Quantidade por pacote** ou outras.

**Quando não usar?** Se o produto é vendido em uma única configuração — por exemplo, uma furadeira que existe somente em 220 V —, informe essa característica na descrição e deixe a seção de variações vazia.

### Etapa A — Cadastrar as características e os valores

1. Abra **Variações do produto**.
2. Clique em **Adicionar característica**.
3. Em **Nome da característica**, digite o que o comprador escolherá: por exemplo, `Voltagem`.
4. Em **Valores**, digite `110 V` e pressione **Enter**; depois `220 V` e **Enter** novamente.
5. Se houver outra escolha, clique em **Adicionar característica** de novo. Exemplo: `Capacidade`, com valores `3 L` e `5 L`.

### Como retirar todas as variações e vender como produto simples

1. No produto, abra **Variações do produto**.
2. Clique em **Remover todas as variações** e confirme. Isso limpa as características e a tabela da tela. Como alternativa, remova a última característica e clique **Gerar / atualizar combinações**.
3. Confira o **Preço de venda** e o **Estoque** gerais, que passarão a valer para o produto sem opções.
4. Clique em **Salvar**. Só então as opções antigas deixam de ser oferecidas na loja.
5. Abra a página pública do produto e confira se aparece **Adicionar ao carrinho** sem pedir seleção.

As combinações antigas são **desativadas**, e o histórico de pedidos permanece disponível. Se existir **reserva ativa em um pedido**, o sistema não permitirá retirar essa combinação; primeiro resolva o pedido ou aguarde a liberação da reserva. Não é necessário excluir o produto inteiro.

### Etapa B — Gerar automaticamente as combinações

Clique em **2. Gerar / atualizar combinações**. No exemplo, o sistema cria quatro linhas:

| Combinação | Preço próprio | Estoque | SKU |
| --- | --- | --- | --- |
| 110 V / 3 L | opcional | opcional | opcional |
| 110 V / 5 L | opcional | opcional | opcional |
| 220 V / 3 L | opcional | opcional | opcional |
| 220 V / 5 L | opcional | opcional | opcional |

O botão **Gerar / atualizar combinações** prepara a tabela, **mas ainda não salva o produto**.

### Etapa C — Definir preço, estoque e SKU

Na tabela **3. Preço, estoque e SKU de cada opção**:

- **Combinação:** mostra automaticamente a escolha do comprador; não é necessário escrever uma por uma.
- **Preço próprio:** deixe vazio para usar o preço geral do produto; preencha se aquela combinação tiver preço diferente.
- **Estoque:** deixe vazio para usar o estoque geral **compartilhado** do produto; preencha uma quantidade se a combinação tiver estoque próprio. **0 significa sem estoque**, não “sem controle”.
- **SKU:** código opcional da combinação, útil para separar versões na separação do pedido.
- **Ativa:** desligue para impedir a venda daquela combinação específica.

**Atenção ao estoque compartilhado:** se o produto tem 10 unidades no estoque geral, duas variações com estoque vazio **compartilham as mesmas 10 unidades**. Não são 10 unidades para cada uma.

### Preencher vários preços ou estoques de uma vez

Se várias linhas terão o mesmo preço ou estoque, clique em **Aplicar preço / estoque a todos**. Informe somente os campos que deseja preencher e confirme.

- Esse comando **preenche todas as combinações** que aparecem na tabela. Revise antes de salvar.
- Deixe um campo vazio na ação para **não alterar** aquele dado.
- Ao aplicar um **novo preço próprio a todas**, eventuais promoções próprias anteriores das combinações são removidas; revise descontos depois.

### Promoção e embalagem específicas de uma combinação

Em uma linha, abra o ícone de **ajustes/detalhes**:

- **Preço promocional próprio:** opcional, deve ser menor que o preço válido daquela combinação.
- **Peso, Altura, Largura e Comprimento:** preencha **os quatro juntos**, apenas se aquela combinação for enviada em uma embalagem diferente. Se não precisar, deixe **todos vazios** para usar as medidas gerais do produto.

Se houver preço próprio, a combinação não herda automaticamente a promoção geral. Confira o preço final mostrado na página pública.

### Exemplo para outros segmentos

| Produto | Características | Resultado |
| --- | --- | --- |
| Ferramenta elétrica | Voltagem: 110 V, 220 V; Potência: 500 W, 800 W | 4 combinações |
| Creme cosmético | Fragrância: Lavanda, Neutra; Volume: 200 ml, 500 ml | 4 combinações |
| Conector industrial | Material: Aço, Inox; Rosca: 1/4, 1/2 | 4 combinações |
| Alimento embalado | Peso: 250 g, 500 g, 1 kg | 3 combinações |
| Camiseta | Tamanho: P, M, G; Cor: Preto, Branco | 6 combinações |

Os exemplos são ilustrativos. **Você pode inventar qualquer nome de característica necessário ao catálogo**, desde que represente uma escolha real de compra.

### Limites e alterações posteriores

O editor aceita até **5 características** e até **150 combinações** por produto. Cada nome/valor pode ter até **120 caracteres**.

Ao editar valores já cadastrados:

1. Altere os valores na parte superior.
2. Clique novamente em **Gerar / atualizar combinações**.
3. Confira a tabela antes de salvar. As combinações que continuam iguais preservam seus dados existentes; combinações que saem do conjunto podem ser **desativadas**.
4. Se houver combinação reservada em um pedido, algumas alterações serão bloqueadas para proteger a compra em andamento.
5. Clique em **Salvar**.

**Se aparecer um aviso para gerar as combinações:** você mudou características ou valores, mas ainda não atualizou a tabela. Clique no botão **Gerar / atualizar combinações** antes de salvar.

Para mais exemplos e regras, consulte **[Variações, medidas e estoque](/admin/ajuda?topico=variacoes)**.

## Passo 6 — Disponibilidade e estoque geral

Na seção **Disponibilidade**:

- **Disponibilidade imediata:** produto para venda com entrega conforme estoque e frete.
- **Sob encomenda:** vendido sob essa condição; confirme os prazos e a forma de atendimento com o cliente.
- **Indisponível:** não permite comprar enquanto estiver nessa condição.
- **Estoque:** quantidade geral do produto. Se não houver controle de saldo, o campo pode ficar vazio; isso não é o mesmo que informar zero.

**Se você usa variações:** o estoque geral pode ser compartilhado pelas combinações cujo campo Estoque esteja vazio. Não confunda estoque geral com estoque individual de cada combinação.

## Passo 7 — Dados para entrega (frete)

Na seção **Dados para entrega**, preencha o peso e as dimensões da **unidade embalada para envio**:

- **Peso embalado:** em **quilogramas (kg)**. Exemplo: para 1 kg e 250 g, informe `1,250`, conforme o formato aceito pelo campo.
- **Altura da embalagem:** em **centímetros (cm)**.
- **Largura da embalagem:** em **centímetros (cm)**.
- **Comprimento da embalagem:** em **centímetros (cm)**.

Não use apenas as dimensões do produto fora da caixa. O sistema e as transportadoras precisam das **medidas reais do pacote**. Dados ausentes ou incorretos podem impedir a cotação automática ou produzir um frete errado.

**Se uma variação usa embalagem diferente**, preencha os quatro campos dela nos detalhes da combinação; caso contrário, as medidas gerais serão usadas.

## Passo 8 — Publicação

Na seção **Publicação**, confira:

- **Ativo:** habilita a exibição pública do produto. Deixe desligado enquanto o cadastro ainda não estiver pronto.
- **Produto em destaque:** permite aparecer nas vitrines de destaque da loja.
- **Permitir cotação pelo WhatsApp:** mostra a opção de contato comercial, conforme as configurações de WhatsApp da loja.

Clique em **Salvar**. Se acabou de criar o produto, a tela abrirá a edição para você poder organizar a galeria e continuar o cadastro.

## Assistente de IA — como melhorar os textos

Nas telas de criação e edição, use o botão **Assistente de IA**, no topo da página.

1. Informe os dados verdadeiros que você já conhece sobre o produto.
2. Peça, por exemplo: *“Melhore a descrição desta furadeira. Ela tem potência de 650 W e é vendida em 110 V e 220 V. Não invente dados técnicos.”*
3. Leia as sugestões de **nome, descrição curta, descrição, SEO e categoria**.
4. Selecione somente os campos que deseja aplicar.
5. Confira o texto no formulário e **clique em Salvar**. A IA não salva nem publica sozinha.

A IA **não deve definir preço, custo, estoque, SKU, peso, dimensões ou especificações não fornecidas**. O administrador configura a integração com Groq; o operador autorizado apenas utiliza a ferramenta. Saiba mais em **[Inteligência Artificial](/admin/ajuda?topico=inteligencia-artificial)**.

## Exemplos de cadastro completo

### Exemplo A — Produto sem variações

Uma **espátula de aço de 20 cm**, vendida somente nesse tamanho:

1. Cadastre nome, categoria, fotos e descrição.
2. Informe custo, preço de venda, disponibilidade, estoque e medidas da embalagem.
3. **Deixe Variações do produto vazia**: o comprador não precisa escolher outra versão.
4. Confira Ativo e salve.

### Exemplo B — Produto com variações

Uma **máquina** disponível em 110 V e 220 V:

1. Cadastre o produto principal e o preço geral.
2. Em **Variações do produto**, adicione **Voltagem** com `110 V` e `220 V`.
3. Clique em **Gerar / atualizar combinações**.
4. Se a versão 220 V custa mais, informe o **Preço próprio** apenas nessa linha.
5. Se as versões possuem estoques separados, preencha o estoque de cada uma. Se compartilham o estoque, use o estoque geral.
6. Informe peso e dimensões gerais ou, se forem diferentes, os quatro dados em **Detalhes** de cada linha.
7. Salve e teste a seleção de voltagem na página pública.

## Como editar um produto existente

1. Acesse **[Produtos](/admin/products)** e encontre o produto pelo nome ou SKU.
2. Clique no botão de editar.
3. Altere apenas o que for necessário. Para mudar opções de compra, **regenere as combinações e confira a tabela**.
4. Salve e abra a página pública para confirmar o resultado.
5. Para pausar a venda, desative **Ativo** ou escolha **Indisponível**, conforme a necessidade. Evite excluir produtos com histórico de pedidos.

Alterar o preço atual do produto **não altera os valores já registrados em pedidos anteriores**.

## Problemas comuns e como resolver

| O que aconteceu? | O que verificar? |
| --- | --- |
| O produto não aparece na loja | **Ativo** ligado, categoria ativa, disponibilidade e endereço correto. |
| Não consigo salvar | Veja os avisos de validação: categoria, preço, SKU duplicado, promoção inválida ou variações sem regenerar. |
| A IA sugeriu informação incorreta | Não aplique; revise manualmente e use apenas dados confirmados. |
| Uma opção não aparece para comprar | Confira se a combinação foi gerada, está **Ativa** e tem disponibilidade. |
| O estoque de duas opções parece igual | Estoque vazio na variação significa **compartilhar estoque geral**. |
| O frete não aparece | Confira peso e as três dimensões da **embalagem** e as configurações de entrega. |
| A foto não aparece | Confira se o upload foi salvo, qual foto está principal e se o armazenamento público funciona. |
| Retirei a última característica, mas a linha continua aparecendo | Clique em **Gerar / atualizar combinações**, ou use **Remover todas as variações**. Depois salve o produto. |
| Não consigo excluir/alterar uma variação | Pode haver reserva ativa em pedido. Não force a operação; revise o pedido. |
| Preço promocional 0,00 gerou erro anteriormente | A partir desta versão, o campo vazio ou 0,00 significa sem promoção. Salve novamente. |

## Checklist final antes de divulgar

- [ ] Nome, categoria e descrição estão corretos.
- [ ] Fotos principais e galeria mostram o produto real.
- [ ] Custo, preço e eventuais descontos foram conferidos.
- [ ] As variações, **se existirem**, foram geradas, revisadas e salvas.
- [ ] Preços e estoques por combinação estão corretos; não há confusão entre estoque próprio e compartilhado.
- [ ] Disponibilidade, peso e medidas da **embalagem** estão corretos.
- [ ] Produto e categoria estão ativos.
- [ ] A página pública foi aberta e, se necessário, testada com uma seleção de variação, carrinho e CEP.

**Regra de ouro:** você só precisa preencher as opções que fazem sentido para o produto, mas preço, disponibilidade e condições reais de entrega precisam estar corretos antes de colocar a loja para vender.
