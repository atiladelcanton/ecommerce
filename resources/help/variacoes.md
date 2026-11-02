## Quando criar variações?

Cadastre variações quando o comprador precisa escolher uma **opção de compra**. Isso serve para qualquer segmento: equipamento `Voltagem=220 V + Capacidade=5 L`, cosmético `Fragrância=Lavanda + Volume=200 ml`, ferramenta `Material=Inox + Comprimento=30 cm` ou camiseta `Cor=Preto + Tamanho=M`. Se só existe uma configuração, **não é necessário** criar variações.

## Como cadastrar — novo formulário simples

O cadastro agora funciona em **três etapas**, parecido com o cadastro de opções da Shopee. Não é preciso escrever a mesma combinação diversas vezes.

1. Acesse **[Catálogo → Produtos](/admin/products)** e crie ou edite um produto.
2. Abra **Variações do produto**. Clique em **Adicionar característica** e informe **qualquer nome que faça sentido para o item**, por exemplo **Voltagem**. O campo é livre: não existe um catálogo fixo de nomes.
3. Em **Valores**, digite `110 V` e pressione **Enter**. Depois `220 V`. Se também houver capacidades diferentes, clique em **Adicionar característica**, informe **Capacidade** e cadastre `3 L` e `5 L`.
4. Clique em **Gerar / atualizar combinações**. O sistema cria uma tabela com 4 linhas: 110 V/3 L, 110 V/5 L, 220 V/3 L, 220 V/5 L.
5. Na tabela, informe **Preço próprio**, **Estoque**, **SKU (opcional)** e **Ativa** conforme cada combinação. Se o preço for igual ao geral do produto, deixe o campo vazio. Se as variações usarem estoque compartilhado, deixe o estoque delas vazio.
6. Se todas tiverem o mesmo preço ou estoque, clique em **Aplicar preço / estoque a todos** e preencha o que desejar. Esse botão não salva: apenas preenche a tabela. Atenção: definir um novo preço próprio para todas remove descontos promocionais próprios anteriores das combinações, para evitar preços inválidos.
7. Para informar promoção própria ou embalagem especial em uma combinação, clique no ícone de **ajustes/detalhes** da linha. Esse passo é opcional.
8. Clique em **Salvar** e confira o resultado na loja pública. **Gerar combinações, sozinho, não salva o produto.**

### Exemplos

- **Equipamento elétrico:** Voltagem 110 V/220 V × Capacidade 3 L/5 L = 4 combinações.
- **Produto de limpeza:** Fragrância Lavanda/Cítrico × Volume 500 ml/1 L = 4 combinações.
- **Alimento:** Peso 250 g/500 g/1 kg = 3 opções.
- **Peça automotiva:** Modelo A/B × Lado Esquerdo/Direito = 4 combinações.
- **Ferramenta:** Material Aço/Inox × Comprimento 30 cm/50 cm = 4 combinações.
- **Camiseta:** Tamanho P/M/G × Cor Preto/Branco = 6 combinações.

### Como alterar depois

Ao editar o produto, os tipos, valores e combinações já existentes aparecem preenchidos. Os preços, estoques, SKUs, medidas e IDs das combinações que permanecerem iguais serão preservados quando você clicar em **Gerar / atualizar combinações**.

Se acrescentar ou retirar um valor de qualquer característica, clique **Gerar / atualizar combinações** antes de salvar. Se esquecer, o sistema avisará. Combinações retiradas são **desativadas ao salvar**, e não apagadas do histórico; variações com reserva ativa não podem ser retiradas dessa forma. As variações antigas desativadas ficam arquivadas no banco para preservar pedidos e **não aparecem na tabela principal**; se precisar voltar a oferecer a mesma combinação, cadastre seus valores e clique em Gerar / atualizar combinações.

São aceitas até **5 características diferentes** e **150 combinações** por produto para preservar a velocidade do formulário. Os nomes e os valores são livres, respeitando o limite de 120 caracteres por campo.

## Como remover todas as variações de um produto

1. Abra **Catálogo → Produtos** e edite o produto desejado.
2. Vá até **Variações do produto** e clique em **Remover todas as variações**. Confirme a ação. Alternativamente, retire a última característica e clique em **Gerar / atualizar combinações**.
3. Confira o **Preço de venda** e **Estoque** gerais, que serão usados quando não houver mais opções.
4. Clique em **Salvar**. A tabela fica vazia e o comprador deixa de precisar escolher uma variação.
5. Teste a compra na loja pública.

As combinações anteriores são desativadas, não apagadas do histórico. **Não será possível removê-las enquanto houver reservas de estoque ativas em pedidos.** Nesse caso, resolva a pendência do pedido antes de tentar novamente.

**Preço promocional (produto ou combinação):** campo vazio ou `0,00` significa **sem promoção**; um desconto real precisa ser maior que zero e menor que o preço de venda.

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

## Característica de compra x especificação técnica

Use **Variações** apenas quando o comprador precisa **escolher entre opções vendidas** (por exemplo, 110 V ou 220 V). Se o equipamento possui somente 220 V, essa informação é uma **especificação do produto** e pode ficar na descrição. Não é necessário criar uma variação. Para peso e dimensões de transporte, use os campos de **Dados para entrega**.

O formulário não está limitado a roupas: os exemplos são sugestões, não opções fixas. Você pode criar características como `Tipo de rosca`, `Sabor`, `Gramatura`, `Espessura`, `Quantidade por pacote`, `Acabamento`, `Comprimento` ou qualquer outra relevante ao seu catálogo.
