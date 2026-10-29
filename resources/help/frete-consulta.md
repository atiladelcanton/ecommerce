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
