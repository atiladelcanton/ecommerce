## Qual inteligência artificial o SABA usa?

O provedor integrado é o **Groq**, com **q** no final (**GroqCloud**). Ele não é o **Grok**, produto diferente. O SABA usa a API do Groq para ajudar a redigir informações de produtos e postagens do blog. O modelo configurável pode ser `openai/gpt-oss-120b` ou `openai/gpt-oss-20b`, conforme as opções que aparecem no painel.

A IA **sugere conteúdo**; você decide o que aplicar. Ela não publica ou altera valores comerciais automaticamente.

## Criar conta no Groq e obter API Key

1. Abra o portal oficial **[console.groq.com](https://console.groq.com/)**.
2. Crie sua conta, confirme o acesso e entre no console.
3. Acesse **[API Keys](https://console.groq.com/keys)**.
4. Gere uma chave para a sua loja, identifique-a corretamente e copie para um local protegido.
5. Confira os limites de uso e eventuais custos do plano no painel da sua conta. A existência de camada gratuita não garante capacidade ilimitada.

Referência oficial: [Guia inicial do Groq](https://console.groq.com/docs/quickstart).

## Configurar no SABA

1. Abra **[Configurações → Inteligência Artificial](/admin/ai-settings)**.
2. Ative **Assistente de IA**.
3. Selecione **Groq** como provedor e escolha o modelo oferecido pelo formulário.
4. Cole sua **API Key**. Após salvar, ela ficará oculta: o painel não deve exibir o segredo novamente.
5. Opcionalmente, defina **Limite mensal interno de gerações** para controlar consumo no SABA. Ele **não substitui os limites do Groq**.
6. Preencha **Instruções da loja** com preferências de tom, público e restrições de comunicação. Exemplo: “Use português claro, evite promessas não comprovadas e não invente especificações”.
7. Clique **Salvar e testar conexão**.
8. Confira os indicadores de uso da IA: gerações, tokens de entrada e saída e falhas.

## Como usar IA para cadastrar um produto

1. Entre em **[Produtos → Criar/Editar](/admin/products)**.
2. Comece pelo que você sabe: nome, função, material, medidas reais, uso, compatibilidades e restrições.
3. Abra **Assistente de IA** e escolha criar texto novo ou aprimorar o cadastro.
4. Escreva instruções específicas, como “Melhore a descrição deste rodo de serigrafia para iniciantes, preservando somente as características informadas”.
5. Confira a resposta sugerida campo por campo: **nome, descrição curta, descrição completa, SEO e categoria sugerida**.
6. Aplique apenas os campos aprovados. A IA modifica o formulário, mas **é necessário clicar Salvar** para persistir.
7. Verifique dados técnicos manualmente antes de publicar.

## Como usar IA em postagens do blog

1. Abra **[Postagens do blog](/admin/blog-posts)** e escolha Criar/Editar.
2. Clique **Assistente de IA**.
3. Escolha **Escrever nova postagem** ou **Melhorar texto existente**.
4. Informe assunto, objetivo, público, informações confirmadas, tom e tamanho do texto.
5. Revise título, resumo, conteúdo formatado e SEO. Aplique somente o que deseja.
6. Ajuste imagem, categoria e etiquetas. **Salvar** é necessário; a publicação depende do status e da data configurados no artigo.

## O que a IA não pode decidir por você

- **Preço de venda, custo e promoção**.
- **Estoque, SKU, peso e dimensões**.
- Características técnicas não fornecidas, compatibilidades não verificadas ou garantias inexistentes.
- Política comercial, direitos autorais, instruções de segurança, requisitos legais ou financeiros.

> **Atenção:** um texto convincente pode estar factualmente errado. Confira sempre antes de aplicar ou publicar, principalmente números e especificações técnicas.

## Se a IA falhar

- **Chave inválida:** gere ou salve novamente a API Key correta.
- **Limite excedido:** consulte contagem mensal no SABA e cotas na plataforma Groq.
- **Texto incompleto ou incoerente:** forneça mais dados, reduza o escopo e peça revisão; não invente atributos para completar campos.
- **Falha de conexão:** use **Salvar e testar conexão**, confira status do provedor e tente novamente após estabilização.

## Exemplo de pedido para a IA — produto

No cadastro de uma máquina de serigrafia, você pode escrever:

> “Melhore a descrição para compradores iniciantes. As características confirmadas são: quatro berços, acionamento manual e estrutura de aço. Não invente capacidade, peso ou medidas. Escreva em português simples, com um resumo curto e uma descrição detalhada.”

Depois confira a prévia. Se a IA disser que a máquina pesa 50 kg sem você ter informado isso, **apague a informação inventada**. Aprove apenas os textos corretos.

## Exemplo de pedido para a IA — blog

> “Crie um artigo para iniciantes com dicas de conservação de quadros de serigrafia. Organize com introdução, subtítulos e conclusão. Não afirme que existe uma técnica universal. Use um tom educativo e convide o leitor a conhecer os produtos da loja sem prometer resultados.”

Você pode aplicar **somente o título**, **somente a descrição** ou **todo o texto**, conforme as opções da tela. O conteúdo não será publicado sem salvar e definir o estado correto da postagem.

## Como economizar gerações e obter textos melhores

- Reúna as informações reais antes de abrir o assistente. Um pedido específico costuma exigir menos retrabalho do que “faça uma descrição bonita”.
- Se o resultado estiver muito longo, peça para resumir em vez de gerar novamente sem orientação.
- Compare cada sugestão com a ficha real do produto e com as regras da sua marca.
- Nunca inclua CPF, cartão, senha, chave de API ou dados pessoais de clientes no texto enviado à IA.
