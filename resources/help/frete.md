## Onde configurar

Abra **[Configurações → Meios de Entrega](/admin/shipping-methods)**. O SABA reúne provedores de frete, retirada e operação manual em uma área específica.

## Antes de ativar frete automático

1. Preencha **Configurações da loja → Frete → CEP de origem**.
2. Confira endereço de remetente, razão social, CNPJ e dados fiscais necessários.
3. Cadastre **peso e dimensões de cada produto e variação**. Peso em kg; altura, largura e comprimento em cm.
4. Crie e verifique sua conta no Melhor Envio.
5. Conecte a conta ao SABA pelo fluxo OAuth descrito no tutorial **Criar conta e conectar o Melhor Envio**.

## Configurar Melhor Envio

1. Em **Meios de Entrega**, localize o card **Melhor Envio** e clique em **Configurar**.
2. A configuração possui áreas/abas para **Conexão**, **Transportadoras e serviços**, **Unidades de postagem** e **Operação**.
3. Em **Conexão**, use ambiente correto (Sandbox ou Produção), credenciais correspondentes e fluxo de autorização da sua conta.
4. Em **Transportadoras e serviços**, **sincronize** o catálogo com o provedor e habilite somente os serviços que realmente deseja oferecer.
5. Serviços novos começam **desativados** e em operação manual; **compra automática** só deve ser habilitada após homologação do serviço na conta.
6. Em **Unidades de postagem**, atualize as agências por filtros de transportadora e localização; escolha a unidade de preferência quando aplicável.
7. Confira configurações operacionais e salve cada aba conforme as instruções do formulário.
8. Ative a integração no card e faça cotações de teste com produtos/CEPs reais e embalagens válidas.

## Por que o checkout pode não oferecer entrega?

- O produto não possui peso e medidas suficientes.
- A transportadora não atende o CEP informado.
- O volume ultrapassa os limites do serviço.
- O serviço está desativado, indisponível ou não foi sincronizado.
- A integração ficou desconectada ou o ambiente está incorreto.

O sistema **não inventa** frete grátis nem medidas. Se não existir opção automática, pode oferecer **Frete sob consulta**, caso permitido pelo fluxo.

## Retirada na loja

No card **Retirada**, clique **Configurar** e preencha **Nome exibido, Endereço da retirada, Instruções e Prazo de preparação**. Salve e ative a opção. A retirada aparece como alternativa independente de etiqueta, quando elegível.

## Outros provedores

Os cards de **SuperFrete** e **Correios direto** podem aparecer como opções futuras. **Isso não significa integração direta funcional**: os Correios apresentados pelo Melhor Envio fazem parte da integração Melhor Envio. Não prometa serviços que ainda não foram implementados/homologados.

## Regras importantes

- A cotação de entrega pertence ao pedido efetuado; gerar etiqueta depois **não deve recalcular silenciosamente o preço contratado**.
- Ao cancelar etiqueta, você não cancela automaticamente a venda.
- Quando houver vários volumes, podem existir várias etiquetas/slots para o mesmo pedido.
