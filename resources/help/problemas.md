## Antes de procurar suporte: diagnóstico rápido

### Produto cadastrado não aparece na loja

1. Confira o produto **Ativo**.
2. Confira a **categoria** ativa e correta.
3. Confirme que o produto não está **Indisponível**.
4. Abra a URL do produto diretamente ou faça uma busca pelo nome/SKU.
5. Caso esteja em promoção, confirme preço promocional válido (maior que zero e inferior ao normal).

### Foto não aparece

- Confira extensão JPG, PNG ou WebP e tamanho de até 5 MB.
- Verifique se a associação de imagem foi salva e se existe foto principal.
- Se várias imagens de toda a loja não aparecem, o armazenamento público pode precisar de ajuste técnico (`storage:link`, permissões ou servidor web). Não mova imagens manualmente para lugares desconhecidos.

### Banner não aparece ou parece “cortado”

- Confira **Ativo**, início/fim e link de mídia.
- Refaça arte de desktop em proporção horizontal recomendada (referência 1600×600), e use imagem mobile adaptada.
- O sistema não exige medidas exatas; altere a composição da arte quando a proporção não corresponder à área de exibição.

### Frete retorna indisponível

1. Confira **CEP de origem** da loja e CEP de destino.
2. Confira **peso em kg** e as três dimensões em cm.
3. Verifique se o serviço/transportadora está habilitado e atendendo a região.
4. Se o item for muito grande, veja restrições de tamanho e peso.
5. Use frete sob consulta, quando oferecido, sem inventar valor de frete nem medidas.

### Cupom não funciona

- Confira código, datas, ativo, limite e subtotal mínimo.
- Veja se a categoria/produto está contemplado e se itens promocionais são permitidos.
- Verifique se o CPF/CNPJ já usou o cupom ou possui reserva pendente.
- Não altere tabelas de cupons diretamente para liberar uso.

### Pagamento consta pendente, mas cliente pagou

1. Consulte a cobrança no painel **Asaas**.
2. Confira **Meios de Pagamento → Últimas cobranças** e o status do pedido.
3. Confira se a URL e token de webhook estão corretos, além do ambiente Sandbox/Produção.
4. **Não marque como pago manualmente** apenas com foto de comprovante: solicite diagnóstico técnico da confirmação.

### Etiqueta não foi gerada

- Confira pedido pago, modo de frete, documento fiscal/de conteúdo, chave de NF-e e medidas.
- Rejeição 422 exige correção dos dados indicados.
- Compra de resultado incerto exige **Verificar envio** antes de qualquer nova tentativa.
- Em Sandbox, algumas impressões/formatos podem não estar disponíveis.

### IA apresenta erro

- Faça **Salvar e testar conexão** na página de IA.
- Confira chave, modelo, ativação e limite mensal interno.
- Se a resposta estiver ruim, forneça dados técnicos melhores e revise manualmente.

### Blog ou seção “Últimas do Blog” não aparece

- Em **Configurações da loja → Blog**, ative o blog.
- Ative separadamente **Exibir “Últimas do Blog”**.
- Confirme que existem artigos com status publicado e data válida.

### Trocar o visual do template

O administrador **não troca templates**. Ele altera logo e cores em Configurações da loja. Para mudar o template inteiro, fale com a equipe técnica da instalação.

## Quando a situação exige suporte técnico

- Erros HTTP 500, páginas em branco, arquivos internos expostos.
- Cobrança/estorno duplicado, webhook suspeito ou pedido marcado pago incorretamente.
- Resultado incerto de compra de etiqueta ainda não resolvido por **Verificar envio**.
- Inconsistência de estoque, documento fiscal incorreto ou violação de autorização de dados.
- Ambiente de testes ligado em loja que deveria cobrar de verdade.

Guarde número do pedido, data/hora, tela da operação e mensagem de erro **sem expor chaves, senhas, tokens ou documentos completos**.
