## Checklist: antes de abrir a loja ao público

- [ ] Nome comercial, razão social, CNPJ e contato conferidos.
- [ ] Logo e cores revisados nos dois tamanhos de tela.
- [ ] WhatsApp abre o número correto.
- [ ] CEP de origem do frete e endereço do remetente corretos.
- [ ] Categorias ativas e produtos vinculados corretamente.
- [ ] Produtos com foto, preço, disponibilidade, peso e dimensões completos.
- [ ] Estoque e variações conferidos.
- [ ] Banners legíveis no desktop e celular; links testados.
- [ ] Meio de entrega configurado, testado e habilitado.
- [ ] Asaas em ambiente **correto**, com webhook testado e Pix/cartão conforme desejado.
- [ ] Compra de teste completa e status conferidos até a etapa esperada.
- [ ] Políticas comerciais/contato conferidos com os responsáveis pela operação.

## Checklist: cotação de entrega negociada

- [ ] Conferir se o pedido está **Pedido recebido / Aguardando cotação**.
- [ ] Conferir transportadora, endereço, preço e prazo com o prestador do transporte.
- [ ] Abrir **Informar cotação de entrega** e preencher todos os campos obrigatórios.
- [ ] Conferir o **total atualizado** do pedido e a validade da proposta.
- [ ] Compartilhar o link assinado: **WhatsApp manual**, **Copiar link** ou **Enviar cotação por e-mail** (com SMTP de verdade).
- [ ] Esperar o **aceite pelo cliente**; não cobrar antes.
- [ ] Esperar a **aprovação Asaas**; não expedir com pagamento apenas pendente.
- [ ] Se o checkout ficou inconclusivo, verificar o Asaas e **não repetir cobrança**.
- [ ] Com o pagamento aprovado, seguir preparação e envio manual.

## Checklist: cadastrar um novo produto

- [ ] Categoria correta.
- [ ] Nome, descrição e dados técnicos verdadeiros.
- [ ] Preço de venda maior que zero; promoção inferior ao preço normal, se houver.
- [ ] Estoque/variações corretos.
- [ ] Fotos nítidas e vídeo opcional verificado.
- [ ] Peso em kg e altura/largura/comprimento em cm, da unidade embalada.
- [ ] Produto ativo e disponibilidade certa.
- [ ] Visualização pública e carrinho testados.

## Checklist: início do dia de trabalho

- [ ] Consultar painel e pedidos que exigem ação.
- [ ] Confirmar pagamentos e revisar cobranças pendentes no Asaas.
- [ ] Iniciar preparação de pedidos pagos.
- [ ] Conferir itens, embalagens e prazo de expedição.
- [ ] Publicar propostas de frete sob consulta e comunicar os clientes.
- [ ] Verificar etiquetas em falha ou operação incerta.
- [ ] Imprimir etiquetas geradas, postar encomendas e guardar comprovantes.
- [ ] Acompanhar rastreamento e resolver ocorrências de entrega.

## Checklist: antes de gerar cada etiqueta

- [ ] Pedido pago, em preparação e frete contratado verificado.
- [ ] Endereço/CEP e dados do remetente conferidos.
- [ ] Embalagem real e número de volumes conferidos.
- [ ] Documento fiscal válido ou declaração permitida à operação.
- [ ] Agência exigida pelo serviço selecionada corretamente.
- [ ] Nenhuma operação de compra em estado incerto aguardando verificação.
- [ ] Após geração, imprimir e conferir todas as etiquetas antes de postar.

## Checklist: criar uma campanha promocional

- [ ] Preço promocional/cupom válido e financeiramente sustentável.
- [ ] Itens contemplados definidos (loja inteira, categorias ou produtos).
- [ ] Se for cupom: mínimo, máximo, prazo e limite de uso conferidos.
- [ ] Se desejar alcançar produtos em promoção, configurar essa permissão explicitamente.
- [ ] Banner com arte adaptada, link correto e agendamento.
- [ ] Teste no checkout com CPF/CNPJ de teste que satisfaça os requisitos.

## Checklist: encerramento semanal

- [ ] Verificar pedidos pagos não enviados.
- [ ] Revisar casos de etiqueta cancelada ou compra incerta.
- [ ] Revisar cobrança em análise e reembolsos pendentes no provedor.
- [ ] Atualizar preços/estoque de produtos desatualizados.
- [ ] Revisar cupons vencidos e campanhas encerradas.
- [ ] Conferir indicadores de pedidos e entregas; **não tratar faturamento como saldo bancário**.

## Regra de segurança

Nunca compartilhe **API Key do Asaas, Groq ou Client Secret do Melhor Envio**, senhas, tokens, dados completos de cartão ou documentos pessoais em grupos. Em situações de cobrança ou etiqueta incerta, consulte o provedor e a equipe técnica antes de repetir uma ação irreversível.

## Checklist opcional — campanha com influenciadores

- [ ] Cadastrei o parceiro em **Vendas → Influenciadores**, defini o percentual e criei acesso ao portal.
- [ ] Criei um cupom em **Vendas → Cupons de desconto**, vinculei o influenciador e revisei limites e validade.
- [ ] Testei o acesso privado em `/influencers/login` e a visualização apenas dos próprios pedidos.
- [ ] Homologuei uma compra e confirmei que a comissão só passou a **prevista** depois do pagamento aprovado.
- [ ] Conferi e aprovei a comissão, realizei o Pix fora do sistema e registrei o repasse com referência.
- [ ] Testei cancelamentos e estornos para não aprovar repasses indevidos.
