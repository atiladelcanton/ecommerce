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
