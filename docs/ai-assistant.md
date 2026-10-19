# Assistente de Inteligência Artificial

## Objetivo

O módulo de IA auxilia o cadastro de produtos sem transformar a IA em fonte de verdade comercial. O assistente pode sugerir nome, descrição curta, descrição completa, título SEO, descrição SEO e categoria. Ele nunca altera automaticamente preço, custo, promoção, estoque, SKU, dimensões, peso ou variações.

## Arquitetura

A camada de catálogo não conhece Groq diretamente. O fluxo usa `AiContentProvider`, `AiProviderRegistry`, DTOs de entrada/saída e a action `GenerateProductContentAction`. Hoje o adapter real é `GroqContentProvider`; OpenAI, Gemini, OpenRouter ou Ollama podem ser adicionados implementando o mesmo contrato e registrando o provider no registry.

## Groq

A integração usa HTTP nativo do Laravel, sem pacote adicional, com base URL `https://api.groq.com/openai/v1`. O teste de conexão consulta `/models`. A geração usa `/responses` com Structured Outputs em JSON Schema `strict: true` nos modelos GPT-OSS suportados.

Modelos disponíveis na tela:

- `openai/gpt-oss-120b` — padrão, melhor qualidade.
- `openai/gpt-oss-20b` — alternativa mais rápida.

A API Key é armazenada usando cast `encrypted` do Laravel e nunca é exibida novamente. Logs não registram API Key, prompt completo ou resposta completa.

## Uso no produto

Nas páginas Criar produto e Editar produto existe a ação **Assistente de IA**. O usuário escolhe criar ou melhorar os textos, pode adicionar informações brutas/ficha técnica, gera uma sugestão e revisa cada campo antes de aplicar. Aplicar a sugestão altera somente o estado do formulário; é necessário clicar em **Salvar** para persistir o produto.

O prompt global proíbe invenção de características técnicas. O campo `store_instructions` permite acrescentar regras de tom e redação da loja.

## Uso e limites

`ai_usage_logs` registra provider, modelo, ação, produto/usuário quando disponíveis, tokens informados pelo provider, duração e sucesso/falha. `monthly_request_limit` é um limite interno opcional e não substitui os limites da conta do provedor.

## Adicionando outro provider

1. Criar uma classe que implemente `App\Domain\AI\Contracts\AiContentProvider`.
2. Implementar `testConnection()` e `generateProductContent()`.
3. Normalizar a resposta em `AiGenerationResult` / `ProductContentSuggestion`.
4. Registrar a implementação em `AiProviderRegistry`.
5. Adicionar seus campos de configuração sem expor segredos.
6. Criar testes com `Http::fake()`; nunca chamar APIs reais durante a suíte.

### GPT-OSS e itens de reasoning

A Responses API pode devolver um item `type=reasoning` antes da mensagem final nos modelos GPT-OSS. O adapter Groq deve ignorar qualquer `reasoning_text` e considerar somente `output[].type=message` com `content[].type=output_text` como conteúdo estruturado do produto. Isso evita tentar interpretar o raciocínio intermediário como JSON.
