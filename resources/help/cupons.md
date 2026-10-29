## O que é um cupom?

É um código digitado pelo cliente no **checkout** para obter desconto sob regras definidas por você. **Não é a mesma coisa que preço promocional**: o preço promocional é exibido diretamente no produto, enquanto o cupom precisa ser aplicado e validado.

## Cadastrar um cupom

1. Abra **[Vendas → Cupons de desconto](/admin/coupons)** e clique em **Novo cupom**.
2. **Código:** informe algo simples, como `BEMVINDO10`. O sistema normaliza o código e impede duplicidade.
3. **Descrição interna:** explique o objetivo da campanha para você e sua equipe.
4. **Tipo de desconto:** escolha **Percentual** ou **Valor fixo**.
5. Se percentual, informe um inteiro de **1% a 100%**. Se fixo, informe valor monetário em reais; a aplicação armazena centavos.
6. Em **Aplicável a**, escolha **Todos os produtos**, **Categorias selecionadas** ou **Produtos selecionados**. Se selecionar categorias/produtos, indique exatamente quais entram na campanha.
7. Decida se o cupom pode abranger **produtos já em promoção**, usando **Permitir produtos já em promoção**.
8. Opcionalmente, defina **Subtotal mínimo** e **Desconto máximo**. O subtotal considera itens, **sem frete**.
9. Opcionalmente, defina **Limite total de utilizações** e datas **Válido a partir de/até**.
10. Ative **Cupom ativo** e salve.
11. Teste com um pedido de exemplo que satisfaz a regra e com outro que não satisfaz.

## Exemplos práticos

**Campanha de 10% na loja:** `BEMVINDO10`, tipo Percentual, valor 10, abrangência Todos, ativo. Sobre subtotal elegível de R$ 200,00, o desconto será R$ 20,00, salvo limites/regras adicionais.

**R$ 30 de desconto em tintas:** tipo Valor fixo, valor R$ 30,00, abrangência Categorias selecionadas → Tintas, com subtotal mínimo se desejar. Só os produtos elegíveis entram no cálculo; não assuma que o frete será descontado.

**Produto já em promoção:** com a opção **Permitir produtos já em promoção** desligada, itens promocionais não entram automaticamente no desconto daquele cupom.

## Uma utilização por CPF/CNPJ

O sistema controla a aplicação e o consumo por **documento do comprador** (CPF ou CNPJ), inclusive para clientes que compram sem criar conta. Em regra, o mesmo documento não pode aproveitar o mesmo cupom novamente após o uso confirmado. Não tente contornar isso criando outra conta com o mesmo documento.

## Reserva, confirmação e cancelamento

1. No checkout, o cliente aplica o código e o sistema valida vigência, ativação, elegibilidade, valor mínimo, limite total e documento.
2. Ao criar o pedido, o uso pode ficar **reservado** enquanto a cobrança aguarda conclusão.
3. **Após pagamento aprovado**, a utilização é registrada como efetiva.
4. Em caso de cancelamento/expiração **confirmados**, a reserva é tratada conforme as regras do sistema. Não libere manualmente a reserva só porque o comprador fechou o navegador.
5. Se o checkout estiver aguardando confirmação do Asaas, não considere o uso liberado até receber o evento definitivo.

## Se o cupom é recusado, revise

- Código digitado corretamente, cupom ativo e dentro das datas?
- O carrinho possui produto/categoria contemplado?
- Há produtos promocionais bloqueados pelo cupom?
- A compra cumpre o mínimo? O desconto máximo está configurado?
- O cupom ou o documento já atingiu o limite?
- Há checkout pendente que ainda reserva o cupom?

> **Não exclua registros de reserva ou de histórico diretamente no banco para “liberar” um cupom**: isso pode permitir reutilização incorreta e prejudicar a auditoria.

## Exemplo completo: criar `CLIENTE10` com 10% de desconto

1. Abra **Cupons de desconto** e clique em criar.
2. Código: `CLIENTE10`. Descrição interna: “Primeira campanha de boas-vindas”.
3. Tipo: **Percentual**. Percentual: **10**.
4. Em **Aplicável a**, escolha **Todos os produtos**. Se quiser restringir, selecione Categorias ou Produtos e informe quais são válidos.
5. Selecione se o desconto também vale para itens que já estão em promoção.
6. Se quiser, coloque **Subtotal mínimo** (por exemplo, R$ 100) e **Limite de utilizações**. Não preencha limite se não deseja limitar a campanha por quantidade.
7. Escolha a data final ou deixe sem vencimento. Marque **Cupom ativo** e salve.
8. Abra a loja, adicione um produto elegível e teste o código no checkout. Se o subtotal de produtos elegíveis for R$ 200, o desconto de 10% será de R$ 20, respeitando as regras que você configurou.

### O que significa “uso reservado”?

Uma pessoa pode aplicar o cupom e iniciar um pedido **sem ter pago ainda**. O sistema pode reservar aquela utilização para não ultrapassar o limite da campanha. Se a venda terminar com pagamento confirmado, vira utilização efetiva. Se ela for cancelada ou expirar segundo o fluxo, a reserva será liberada conforme a confirmação do sistema.

**Exemplo:** um cupom com limite total de 2 usos pode parecer esgotado com 1 venda paga e 1 checkout ainda pendente. Não aumente o limite nem limpe o banco antes de investigar os pedidos pendentes.

### Dúvidas comuns

**O desconto inclui frete?** Não: as regras consideram os produtos elegíveis, não o valor da entrega.

**O mesmo cliente pode usar a campanha novamente com outro cadastro?** A proteção usa CPF/CNPJ; mudar e-mail não altera a regra.

**Criar cupom altera o preço cadastrado?** Não. O desconto é aplicado ao pedido válido no checkout.
