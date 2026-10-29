## 1. Criar sua conta Asaas

O **Asaas** é o provedor que recebe as cobranças do checkout desta loja. Para cadastrar-se, acesse o site oficial: **[asaas.com](https://www.asaas.com/)**. Utilize seus dados reais de empresa ou pessoa conforme o tipo de conta e conclua a verificação solicitada pelo provedor.

- Para desenvolvimento e testes, o Asaas possui um ambiente separado: **[sandbox.asaas.com](https://sandbox.asaas.com/)**.
- **Sandbox e Produção são contas independentes**, com dados, chaves e URLs distintas. Nunca use a chave de teste em produção.
- O próprio Asaas decide requisitos de validação, taxas, limites e habilitação de métodos de cobrança. Confirme esses dados diretamente no painel da sua conta.

## 2. Gerar uma API Key

1. Entre no painel web do Asaas **no ambiente correto**.
2. Abra a área **Integrações → Chaves de API** (os nomes da interface podem sofrer alterações).
3. Crie uma chave nova, com identificação que permita reconhecer seu e-commerce.
4. **Copie e guarde em local seguro imediatamente**: segundo a documentação oficial, a chave é exibida uma única vez.
5. Não coloque a chave no frontend, código-fonte público, planilhas compartilhadas, prints ou mensagens em grupos.

Referência oficial: [Como criar e gerir chaves de API no Asaas](https://docs.asaas.com/docs/chaves-de-api).

## 3. Configurar no SABA

1. Abra **[Configurações → Meios de Pagamento](/admin/payment-methods)**.
2. Escolha **Sandbox** ou **Produção**.
3. Cole a **API Key** no campo correspondente.
4. Defina um token longo e secreto para autenticação de webhooks (pelo menos **32 caracteres**); ele deve ser igual na configuração do Asaas e do SABA.
5. Defina Pix, cartão, validade do checkout e parcelamento conforme sua operação.
6. Clique **Salvar** e depois **Testar conexão salva**.

## 4. Configurar webhook

1. Na tela de Meios de Pagamento do SABA, copie a **URL do webhook Asaas exibida pelo painel**. Não invente uma URL.
2. No painel do Asaas, procure configuração de **Webhooks** e cadastre a URL.
3. Configure os eventos **CHECKOUT_CREATED, CHECKOUT_PAID, CHECKOUT_CANCELED e CHECKOUT_EXPIRED** indicados pelo SABA.
4. Configure o **mesmo token de autenticação** definido no SABA.
5. Salve, realize compra no Sandbox e confirme se o pedido muda de **Recebido** para **Pago** após notificação válida.

Referências: [documentação inicial do Asaas](https://docs.asaas.com/docs/visao-geral), [ambiente Sandbox](https://docs.asaas.com/docs/sandbox) e [API Keys](https://docs.asaas.com/docs/chaves-de-api).

## 5. Passar de testes para produção

- Crie/use conta **real** de produção, com aprovação e cadastros exigidos pelo Asaas.
- Gere **nova chave de produção** e configure novo webhook para o ambiente real.
- No SABA, troque o seletor para **Produção** e informe as credenciais **desse** ambiente.
- Teste uma cobrança real controlada, incluindo confirmação do webhook, antes de anunciar a loja como operacional.

## Erros comuns

- **401/sem conexão:** chave inválida ou ambiente incorreto.
- **Cobrança aprovada no Asaas, pedido pendente:** webhook não chegou, URL ou token divergente, ou evento ainda não processado.
- **Cancelamento não liberou reserva:** aguarde confirmação terminal autenticada; investigue no painel Asaas e com suporte técnico.

**Importante:** este roteiro ensina a **criar e conectar sua conta**, não concede acesso ao dinheiro da empresa nem determina taxas cobradas pelo provedor.

## Roteiro de verificação para quem nunca configurou pagamentos

1. **Conta:** entre no site oficial Asaas, cadastre-se e conclua as verificações exigidas para receber pagamentos.
2. **Ambiente:** decida se você está testando (Sandbox) ou recebendo cobranças reais (Produção). Mantenha as chaves separadas.
3. **Chave:** crie uma chave de API no painel do ambiente escolhido e copie-a para **Meios de Pagamento** no SABA. Não cole em um campo de endereço ou em conversas públicas.
4. **Webhook:** copie a URL mostrada pelo SABA, cadastre no Asaas com o mesmo token secreto e ative os eventos necessários. Essa etapa é essencial para que a loja saiba que o pagamento foi confirmado.
5. **Teste:** faça uma compra com valor de teste. Abra o pedido em SABA e confirme que ele mudou de **Recebido** para **Pago** somente depois do evento válido.
6. **Conferência final:** a cobrança aparece no painel do Asaas e o pedido correspondente no SABA? Se não, revise ambiente, URL, token e eventos antes de ativar produção.

**Pergunta comum — “O cliente voltou para o site; o pedido já está pago?”** Não necessariamente. O retorno do navegador, sozinho, não comprova o pagamento. Verifique a confirmação registrada pelo provedor.

**Pergunta comum — “O SABA devolve automaticamente o dinheiro se eu cancelar?”** Não. Consulte o procedimento de pós-venda e execute o estorno no meio de pagamento aplicável quando necessário.
