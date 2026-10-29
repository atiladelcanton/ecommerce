## 1. Abrir uma conta no Melhor Envio

O **Melhor Envio** calcula e intermedeia serviços de transportadoras e a geração das etiquetas. Acesse o site oficial **[melhorenvio.com.br](https://melhorenvio.com.br/)** e escolha **Cadastrar**.

1. Preencha dados de identificação, contato e senha solicitados pelo Melhor Envio.
2. Complete o cadastro do titular e os dados que o provedor exigir.
3. Siga o processo de **verificação de conta**: é possível que o painel peça confirmação de identidade, foto e documento. Sem verificação, a emissão de etiquetas pode ficar bloqueada.
4. Faça login, confira seus dados e leia as regras de postagem, seguros e limites das transportadoras.

Referências oficiais: [Como se cadastrar e entrar](https://centraldeajuda.melhorenvio.com.br/hc/pt-br/articles/31220393418516-Como-se-cadastrar-e-fazer-login-na-plataforma-do-Melhor-Envio) e [Como verificar a conta](https://centraldeajuda.melhorenvio.com.br/hc/pt-br/articles/31220416677780-Como-fa%C3%A7o-a-verifica%C3%A7%C3%A3o-da-minha-conta-no-Melhor-Envio).

## 2. Preparar o SABA

Antes de conectar:

- Em **[Configurações da loja](/admin/store-settings)** informe o endereço e **CEP de origem**, dados fiscais e contato do remetente.
- Em **[Produtos](/admin/products)** preencha peso e três dimensões de cada produto/variação.
- Tenha à mão o endereço público da loja e a **URL exata de callback** informada pela configuração do Melhor Envio no SABA.

## 3. Cadastrar o aplicativo de integração (etapa técnica/administrativa)

1. No painel do Melhor Envio, entre em **Integrações → Área Dev.**
2. Clique em **Cadastrar aplicativo**.
3. Preencha nome, site da plataforma, e-mails de contato e descrição conforme solicitado.
4. Informe a **URL de redirecionamento (callback)** exatamente igual à indicada no SABA. Qualquer diferença pode causar erro `Client invalid`.
5. A documentação do projeto recomenda deixar **desmarcada** a opção para alterar transportadoras e serviços durante a instalação; esses itens serão administrados no SABA.
6. Após salvar, tenha acesso ao **Client ID** e **Client Secret**. Não compartilhe o segredo ou o salve em locais públicos.

Referências oficiais: [Criando um aplicativo](https://docs.melhorenvio.com.br/docs/criando-um-novo-aplicativo) e [Fluxo de autorização](https://docs.melhorenvio.com.br/reference/fluxo-de-autoriza%C3%A7%C3%A3o).

> Dependendo do tipo de integração e das permissões de sua conta, a criação do aplicativo pode ser feita pelo responsável técnico. O lojista pode realizar a autorização da própria conta depois que o aplicativo estiver configurado.

## 4. Conectar pelo SABA (OAuth)

1. Entre em **[Configurações → Meios de Entrega](/admin/shipping-methods)**.
2. No card **Melhor Envio**, clique **Configurar** e abra a aba **Conexão**.
3. Selecione o ambiente adequado e configure os dados do aplicativo exigidos pelo formulário.
4. Use a ação de **conectar/autorizar** para abrir o Melhor Envio.
5. No portal do Melhor Envio, autentique-se com a sua conta e confirme as permissões necessárias.
6. Aguarde o retorno ao SABA e verifique o estado de conexão.
7. Não cole manualmente tokens de acesso em telas públicas nem envie códigos de autenticação por mensagens.

## 5. Selecionar transportadoras e serviços

1. Abra a aba **Transportadoras e serviços** da configuração.
2. Clique em **Sincronizar transportadoras** e aguarde atualização.
3. Ative **apenas** os serviços que deseja oferecer aos clientes.
4. Confira o modo de operação: novos serviços começam desativados e/ou manuais. A compra automática requer testes e homologação específicos.
5. Abra **Unidades de postagem**, atualize por cidade/UF/transportadora e configure a unidade compatível quando o serviço precisar.
6. Revise as opções de **Operação**, salve e, no card principal, ative a integração.
7. Simule frete com CEPs e volumes conhecidos para validar preços, limites e disponibilidade.

## 6. Como é a compra de uma etiqueta?

A venda é primeiro aprovada no SABA. Depois, no pedido, o operador entra em **Em preparação**, escolhe **Gerar etiqueta**, preenche NF-e/declaração aplicável, confirma a compra do frete, imprime e posta. **Não tente comprar etiqueta de pedido ainda não pago**.

## 7. Custos, crédito e problemas

- A conta do Melhor Envio pode exigir **saldo ou meio de pagamento** para comprar frete: verifique as condições na própria conta.
- Etiquetas em Sandbox têm limitações e não representam necessariamente a operação comercial real.
- Se a compra ficar incerta, use **Verificar envio** no SABA antes de tentar novamente.
- Serviços e agências têm regras próprias; não invente identificadores ou medidas para contornar rejeições.

Referências: [Autenticação e conexão](https://docs.melhorenvio.com.br/docs/autenticacao), [Como gerar um envio](https://centraldeajuda.melhorenvio.com.br/hc/pt-br/articles/31220440109844-Qual-o-passo-a-passo-para-gerar-um-envio).

## Roteiro para testar a integração antes da primeira venda

1. Cadastre-se no **Melhor Envio** e finalize a confirmação de identidade e dados de contato solicitados.
2. Confira no SABA o **CEP de origem** da loja e o endereço completo do remetente.
3. Cadastre um produto com peso e três dimensões reais. Sem essas informações, o frete automático pode não ser calculado.
4. Configure ou autorize o aplicativo Melhor Envio na área de **Meios de Entrega**. Se o sistema pedir Client ID, Client Secret e callback, use exatamente os dados do aplicativo criado no painel do provedor.
5. Sincronize transportadoras e selecione os serviços desejados; confirme se a integração aparece conectada e ativa.
6. Acesse a loja como cliente e faça uma simulação para um CEP atendido. Confira valor, prazo e transportadora.
7. Faça uma venda de teste no ambiente apropriado, acompanhe o pedido pago e experimente a geração de etiqueta conforme as regras da plataforma.

### Se a cotação não aparecer

Verifique na ordem: CEP de origem, CEP de destino, peso, altura, largura, comprimento, situação da conexão, serviços habilitados e limites de tamanho/peso de cada transportadora. Não corrija o problema com medidas fictícias: uma transportadora pode recusar ou cobrar diferença depois.

### Se o Melhor Envio disser que a compra da etiqueta falhou

Confira a mensagem e **Verificar envio** no pedido. Em caso de resultado incerto, é possível que a compra já tenha ocorrido. Repetir a operação pode gerar cobrança duplicada.
