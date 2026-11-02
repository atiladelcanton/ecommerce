## O que significa “Frete sob consulta”?

Alguns produtos não possuem frete automático — por exemplo, máquinas grandes, móveis ou entregas feitas por transportadora contratada separadamente. Nesses casos, o cliente pode **solicitar uma cotação** em vez de desistir da compra. O pedido recebe um número, mas **ainda não está pago** e o total fica **A definir** até a loja informar o frete.

**Não crie outro pedido para cobrar o frete.** Tudo acontece no pedido original, com o mesmo número, os mesmos produtos e os mesmos dados do cliente.

## Etapa 1 — O cliente solicita a cotação

1. O cliente coloca os produtos no carrinho e preenche os dados do checkout.
2. Quando não há entrega automática elegível, seleciona **Solicitar cotação de entrega**.
3. O pedido é registrado em **Vendas → Pedidos**, com a situação **Aguardando cotação do frete**.
4. O cliente vê **Frete: A definir**, **Total: A definir** e **Pagamento: Aguardando**. Nenhuma cobrança é criada nessa etapa.
5. Os produtos de pronta entrega ficam reservados inicialmente por **24 horas**. Esse prazo não é o prazo de validade da cotação: são coisas diferentes.

## Etapa 2 — O administrador informa o valor

1. Entre no administrativo e abra **[Vendas → Pedidos](/admin/orders)**.
2. Localize o pedido (por exemplo, `PED-000017`) e clique para abrir os detalhes.
3. Selecione **Informar cotação de entrega**.
4. Preencha **Transportadora** (quem transportará), **Serviço / descrição**, **Valor do frete**, **Prazo estimado** em dias úteis, **Instruções** para o cliente e **Válida até**.
5. Confira o endereço completo e o valor negociado com a transportadora. Salve.
6. O e-commerce atualiza automaticamente o valor do frete e o **total do mesmo pedido**. Os produtos, quantidades e preços já negociados permanecem como estavam.

**Exemplo:** produtos R$ 2.000,00 + frete R$ 280,00 = **R$ 2.280,00**. Se houver cupom, o desconto do pedido continua no cálculo.

A situação fica **Frete cotado — aguardando confirmação**. Ainda não existe pagamento aprovado.

## Etapa 3 — Enviar a cotação ao cliente

Depois de publicar uma proposta válida, o pedido mostra as opções:

- **Abrir link do cliente:** visualiza a página que o comprador receberá, com o preço final.
- **Copiar link para o cliente:** copia a URL assinada para enviar por qualquer canal.
- **Compartilhar pelo WhatsApp:** abre o WhatsApp com uma mensagem pronta. O administrador precisa confirmar o envio; não é um envio automático de mensagens.
- **Enviar cotação por e-mail:** solicita o envio da proposta para o **e-mail cadastrado no pedido**. O servidor precisa ter **SMTP / MAIL_MAILER** configurado para envio real. Se estiver configurado com o driver `log`, o e-mail será registrado no log, mas não chegará ao comprador. Um envio aceito pelo servidor de e-mail não garante que a mensagem chegou à caixa de entrada.

**Atenção:** o link contém uma assinatura e uma validade. É particular; não publique em redes sociais. Para um cliente com conta, o sistema também exige login da conta titular.

## Etapa 4 — O cliente aceita a proposta

1. O cliente abre o link recebido ou entra em **Minha conta → Meus pedidos**.
2. A página mostra transportadora, serviço, prazo, validade, observações, produtos, descontos, frete e total.
3. O cliente clica em **Aceitar frete e ir para pagamento**.
4. O sistema confere a validade e verifica novamente o estoque, caso a reserva inicial tenha vencido.
5. O frete muda para **Frete confirmado**, e o sistema prepara a cobrança usando o **mesmo pedido**.
6. Se o Asaas estiver configurado, o cliente segue para o **checkout hospedado do Asaas**, onde escolhe Pix ou cartão, conforme as opções permitidas pela loja.

O clique no botão **não aprova o pagamento**. A compra só fica paga após confirmação efetiva do Asaas.

## Etapa 5 — Cobrança e aprovação

- **Pagamento pendente:** o checkout existe, mas o Asaas ainda não confirmou. O estoque controlado permanece reservado enquanto a cobrança remota estiver pendente.
- **Pagamento aprovado:** o webhook ou a conciliação validada pelo Asaas confirma o valor e o pedido passa para **Pedido pago**. O estoque é efetivamente baixado.
- **Cliente fechou a aba:** pode abrir **Minha conta → Meus pedidos** e continuar a cobrança pendente sem criar outro pedido.
- **Cobrança recusada ou cancelada pelo Asaas:** depois da confirmação do provedor, o cliente pode tentar uma nova cobrança, sujeito à disponibilidade de estoque e às regras de cupom.
- **Criação do checkout inconclusiva:** se houve falha de rede ou resposta indefinida, o sistema **não** cria outra cobrança automaticamente. O administrador deve verificar o painel Asaas e resolver a pendência antes de tentar novamente.

**Em ambiente local com Asaas Sandbox:** o provedor precisa de uma **URL de retorno HTTPS pública**. Um `APP_URL=http://localhost:8081` não é suficiente para receber redirecionamento e webhooks reais. Configure um endereço HTTPS de teste, com endpoint de webhook acessível, antes de homologar.

## Etapa 6 — Preparar e enviar o pedido

Depois que constar **Pedido pago**:

1. Abra o pedido em **Vendas → Pedidos** e clique **Iniciar preparação**.
2. Separe os itens e confira as embalagens.
3. Para entrega manual/transportadora externa, use o envio manual com transportadora e rastreio, quando disponíveis.
4. **Não gere etiqueta do Melhor Envio** para um frete contratado diretamente com outra transportadora.
5. Registre o envio e acompanhe a entrega normalmente.

## O cliente não aceitou a cotação. O que fazer?

Se o cliente não aceitar a proposta, **não inicie uma cobrança**. Se a cotação vencer, publique uma **nova proposta no mesmo pedido**. O link antigo deixa de valer; o histórico de revisões continua guardado.

Se ele já aceitou e existe uma cobrança Asaas pendente, **não altere o preço do frete**, nem cancele o pedido liberando estoque. Primeiro verifique e encerre a cobrança junto ao provedor; aguarde a confirmação oficial.

## Onde o cliente acompanha?

- **Página de confirmação:** mostra frete a definir, cotação disponível, pagamento pendente ou aprovado, conforme a etapa.
- **Minha conta → Pedidos:** apresenta o frete atualizado, o total e o botão de aceitar cotação ou continuar pagamento, quando aplicável.
- **Página de pagamento:** informa se a cobrança está pendente, aprovada ou em revisão e oferece o link do Asaas quando seguro.

## Problemas comuns

**A cotação venceu:** abra o pedido e publique outra proposta com prazo novo.

**O cliente perdeu o link:** abra o pedido e copie ou envie novamente o link ainda válido.

**O cliente vê “estoque indisponível”:** a reserva inicial pode ter vencido e outro cliente comprou o produto. Confira o estoque antes de prometer a entrega.

**O e-mail não chegou:** confira `MAIL_MAILER`, SMTP, domínio remetente e logs. Use o link pelo WhatsApp como alternativa.

**O pedido parece parado após um pagamento:** o worker de filas precisa estar em execução. Com `QUEUE_CONNECTION=database`, configure `php artisan queue:work` no servidor (Supervisor ou serviço equivalente) e o cron do Laravel. Não é necessário Redis.

**O cliente pagou mas o pedido não mudou para Pago:** verifique a cobrança correspondente no Asaas, o valor, o ambiente e a recepção dos webhooks. Não marque como pago somente porque o cliente enviou uma captura de tela.

**A cobrança está em análise:** não repita o checkout. Confira o painel Asaas ou solicite apoio técnico para conciliar o pagamento.

**O frete mudou depois do aceite:** não edite a proposta enquanto existe cobrança pendente. Primeiro resolva o pagamento existente, com cuidado para não gerar cobrança duplicada.
