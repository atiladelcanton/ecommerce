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
