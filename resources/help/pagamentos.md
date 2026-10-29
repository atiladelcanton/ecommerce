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
