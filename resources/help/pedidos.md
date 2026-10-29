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
