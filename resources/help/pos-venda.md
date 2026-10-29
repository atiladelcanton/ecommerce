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
