## Para que serve o módulo Influenciadores?

Ele permite que a loja tenha parceiros que divulgam **cupons de desconto**. Quando um cliente usa o cupom de um influenciador, o e-commerce registra o pedido e calcula a comissão conforme as regras configuradas.

Você não precisa abrir uma conta Nuvemshop nem conectar outro aplicativo: **o painel de influenciadores funciona dentro desta instalação do e-commerce**, no endereço `/influencers`. Ele é separado do `/admin` e de **Minha conta** dos compradores.

## 1. Cadastre o influenciador

1. Entre em **Vendas → Influenciadores** no painel administrativo.
2. Clique em **Novo influenciador**.
3. Digite o nome e o e-mail que o parceiro utilizará para acessar seu painel.
4. Crie uma senha inicial com **pelo menos dez caracteres**. Compartilhe-a por um canal seguro.
5. No campo **Comissão (%)**, informe a porcentagem desejada: `5` equivale a **5%**, `7,5` a **7,5%** e `10` a **10%**. O limite é 100%.
6. Opcionalmente, informe Instagram, TikTok e chave Pix. Não é necessário fornecer dados bancários para acompanhar vendas.
7. Deixe **Influenciador ativo** ligado e salve.

**Atenção:** o cadastro **não cria automaticamente um cupom**. O influenciador precisa ter um ou mais cupons vinculados.

## 2. Aproveite a tela de cupons já existente

1. Acesse **Vendas → Cupons de desconto**.
2. Clique em **Novo cupom** ou edite um cupom sem utilizações anteriores.
3. Informe o código que o público vai digitar, por exemplo, `MARIA10`.
4. No campo **Influenciador (opcional)**, escolha o parceiro. Para criar um cupom comum da loja, deixe o campo vazio.
5. Configure o desconto oferecido ao comprador: percentual ou valor fixo. **O desconto do cliente é diferente da comissão do influenciador.**
6. Escolha onde o cupom vale: todos os produtos, categorias selecionadas ou produtos específicos.
7. Revise subtotal mínimo, desconto máximo, data de início, data final, limite total e permissão de acumular com produtos em promoção.
8. Marque **Cupom ativo** e salve.

O cadastro de cupons mantém a regra original de **uma utilização/reserva por CPF/CNPJ e por código**. Não é possível transferir um cupom para outro influenciador depois que algum pedido usou ou reservou esse código, para não transferir comissões retroativamente.

## 3. Entenda o cálculo da comissão

A comissão considera apenas o valor dos produtos **após o desconto do cupom**, **sem incluir frete**.

**Exemplo:** cliente compra R$ 300,00 em produtos; usa um cupom com R$ 30,00 de desconto; paga R$ 25,00 de frete. O influenciador tem comissão de 5%.

- Base da comissão: R$ 300,00 − R$ 30,00 = **R$ 270,00**.
- Comissão prevista: 5% × R$ 270,00 = **R$ 13,50**.
- Frete de R$ 25,00: **não participa** do cálculo.

O percentual é registrado no pedido. Se você mudar a taxa do influenciador amanhã, **as comissões dos pedidos anteriores não mudam**.

## 4. Acompanhe os estados dos pedidos e das comissões

| Situação | Significado para o parceiro |
| --- | --- |
| **Pedido pendente** | O cliente finalizou o carrinho, mas o pagamento ainda não foi confirmado. O valor **não está disponível para repasse**. |
| **Prevista** | O pagamento foi confirmado e a comissão foi calculada, mas a loja ainda não a aprovou. |
| **Aprovada** | A equipe autorizada analisou a comissão; o pagamento ao parceiro ainda precisa ser realizado. |
| **Paga** | Um Administrador registrou no painel que **já fez o repasse**. O sistema não transfere Pix automaticamente. |
| **Cancelada** | O pedido não concluiu a compra ou a reserva foi liberada antes do pagamento. |
| **Em revisão** | Houve cancelamento após pagamento ou outro problema financeiro; é necessário conferir se existe reembolso. |
| **Revertida** | A comissão foi anulada manualmente por Administrador, com justificativa. |

**Nunca aprove nem pague comissões de pedidos pendentes.** Se houver cancelamento depois do pagamento, confira o Asaas e a situação do reembolso antes de decidir sobre a comissão. Cancelar um pedido, por si só, não é comprovante de reembolso financeiro.

## 5. Como aprovar e registrar um repasse

1. Vá em **Vendas → Comissões de influenciadores**.
2. Localize o parceiro, o número do pedido, a base de cálculo e a situação.
3. Depois de conferir o pagamento do cliente, utilize **Aprovar** na comissão prevista.
4. Faça a transferência Pix por sua instituição financeira. Esta operação ocorre **fora do e-commerce**.
5. **Somente depois que a transferência for concluída**, clique em **Registrar repasse**.
6. Se precisar anular uma comissão ainda não paga, utilize **Reverter** e informe a justificativa. Se a comissão já foi paga e depois houve reembolso, trate o valor com o parceiro fora do sistema: **não há recuperação Pix automática**.

Ações de aprovar, registrar pagamento e reverter são restritas aos Administradores. Ao registrar o repasse, informe uma **referência do Pix ou comprovante**. Outros operadores autorizados podem consultar os valores, mas só o Administrador cadastra influenciadores e altera a taxa, a senha e os dados financeiros.

## 6. Como o influenciador acessa o próprio painel

1. Envie o endereço da sua loja seguido de `/influencers/login`.
2. O parceiro entra com o e-mail e a senha cadastrados.
3. Na **Visão geral**, verá seus cupons, volume de pedidos, vendas confirmadas, comissões previstas, aprovadas e pagas.
4. Em **Pedidos**, acompanhará as vendas atribuídas, sem visualizar nome, CPF, e-mail, telefone ou endereço dos compradores.
5. Em **Comissões**, verá o valor calculado por pedido e o status.
6. Em **Pagamentos**, verá apenas os repasses marcados como pagos pelo administrador, com data e valor.
7. Em **Meu perfil**, poderá atualizar redes sociais, chave Pix e senha.

O influenciador não acessa o Filament administrativo, outras contas de influenciadores nem dados pessoais dos clientes.

## 7. Como suspender um influenciador ou um cupom

Para suspender o acesso ao portal, abra **Vendas → Influenciadores**, edite a pessoa e desative **Influenciador ativo**. O cadastro e o histórico permanecem intactos.

**A desativação do perfil não cancela cupons automaticamente.** Se quiser impedir novos descontos, abra **Vendas → Cupons de desconto** e desative também os códigos correspondentes. Vendas e comissões anteriores continuam no histórico.

## 8. Perguntas frequentes

**O cupom dá desconto e também paga comissão?** Sim. O desconto reduz o valor pago pelo comprador; a comissão remunera o parceiro conforme a taxa cadastrada.

**Posso ter mais de um cupom por influenciador?** Sim. Todos os cupons vinculados aparecem na conta do parceiro.

**Posso usar um cupom sem influenciador?** Sim, normalmente. Deixe **Influenciador** em branco no cadastro.

**Posso associar um cupom usado anteriormente a um influenciador?** Não. Depois do primeiro pedido atribuído, a associação fica protegida para não alterar a titularidade histórica.

**O influenciador vê o CPF do comprador?** Não. O painel mostra apenas dados de pedidos e comissões pertinentes ao parceiro.

**Existe transferência automática Pix, gamificação, ranking ou fechamento mensal?** **Não nesta primeira versão integrada.** O registro de repasses é manual. A gamificação e os fechamentos do projeto antigo são funcionalidades que poderão ser migradas depois, sem reintroduzir uma segunda base de pedidos.

**Preciso manter o projeto antigo da Nuvemshop funcionando?** Não para usar este novo portal. A integração nativa obtém os pedidos diretamente do e-commerce atual. O projeto antigo foi utilizado como referência de funcionamento e visual, mas seus dados históricos da Nuvemshop não são importados automaticamente.

## Checklist de implantação

- [ ] Cadastrei o influenciador e configurei a taxa de comissão.
- [ ] Criei um cupom ativo e o vinculei ao parceiro.
- [ ] Testei o acesso em `/influencers/login` com uma conta de teste.
- [ ] Confirmei que um pedido pendente aparece sem gerar valor disponível para repasse.
- [ ] Homologuei o pagamento no Asaas Sandbox e a comissão prevista.
- [ ] Testei a aprovação e o registro manual de repasse.
- [ ] Confirmei que o parceiro não consegue consultar pedidos ou comissões de outro influenciador.
