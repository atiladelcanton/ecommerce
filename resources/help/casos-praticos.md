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
