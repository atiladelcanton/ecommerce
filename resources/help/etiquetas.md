## O que é uma etiqueta de envio?

É o documento gerado pela transportadora para identificar e encaminhar a encomenda. **Gerar etiqueta**, **comprar frete**, **imprimir**, **postar** e **entregar** são etapas distintas. Uma etiqueta impressa não significa que a transportadora já recebeu o pacote.

## Antes de clicar em “Gerar etiqueta”

1. Abra **[Vendas → Pedidos](/admin/orders)** e selecione o pedido.
2. Confirme **pagamento aprovado** e status **Em preparação**. Se o pedido está apenas “Recebido”, verifique o pagamento ou a cotação.
3. Separe itens e confira **quantidade, variação, tamanho, peso e embalagem real**.
4. Verifique endereço, CEP, destinatário e dados de remetente (incluindo CNPJ/inscrição estadual quando exigidos).
5. Confira o método de frete contratado, transportadora e serviço. Nem todos os serviços usam as mesmas regras de agência, seguro e documentos.
6. Tenha o documento de transporte adequado à operação: **NF-e** ou, quando legalmente cabível, **Declaração de conteúdo**.

## Gerar etiqueta no painel

1. Com pedido pago, clique **Iniciar preparação**.
2. No pedido **Em preparação** com frete automático, clique em **Gerar etiqueta**.
3. Revise o quadro **Entrega contratada**: serviço, transportadora, volumes e valor pago pelo cliente.
4. Selecione **Tipo de documento**: **Nota Fiscal (NF-e)** ou **Declaração de conteúdo**.
5. Para **NF-e**, informe a chave **real** de 44 dígitos da NF-e **modelo 55** emitida externamente. Uma chave inventada, mesmo com tamanho e dígito corretos, pode ser recusada pelo provedor.
6. Para **Declaração de conteúdo**, use a opção **somente quando a legislação e a transportadora permitirem**. O sistema compõe itens, quantidades e valores a partir do pedido. Se você já tiver uma DC-e externa, pode informar sua chave opcionalmente.
7. Se aparecer **Unidade preferida de postagem**, escolha uma unidade elegível. Ela só é obrigatória quando a regra do serviço exigir.
8. Confirme o envio. O sistema inicia a criação/compra e a geração de etiquetas de acordo com as capacidades do serviço; **uma confirmação pode envolver mais de uma etiqueta**.
9. Abra a etiqueta pela ação **Abrir / imprimir etiqueta**. Na produção, podem aparecer formatos adicionais para baixar, como PDF, ZPL e JPEG, conforme o provedor.
10. Fixe a etiqueta correta na embalagem correspondente e **poste a encomenda** no ponto/transportadora indicado. Guarde comprovante de postagem.

## O sistema emite nota fiscal?

**Não.** O SABA solicita os dados necessários para o Melhor Envio, mas **não emite NF-e**. Emita o documento fiscal aplicável em seu emissor/contabilidade e só depois informe a chave no painel. Em caso de dúvida sobre possibilidade de declaração de conteúdo, consulte seu contador e as exigências do serviço.

## Vários volumes

Um único pedido pode ter diferentes envios/volumes. Verifique os links e status de **todos** os volumes. Não considere o pedido pronto se ainda faltar etiqueta de algum volume.

## E após gerar?

- **Etiqueta gerada:** pronta para imprimir/postar.
- **Pronto para envio:** a expedição está preparada; ainda não é confirmação de postagem.
- **Enviado:** o provedor confirmou postagem, ou houve fluxo manual/contingência válido.
- **Entregue:** existe confirmação de entrega.

## Se algo der errado

Não clique repetidamente em **Gerar etiqueta** diante de resultado incerto. Vá para o tutorial **Ações da etiqueta e solução de falhas**: a ação **Verificar envio** existe justamente para evitar compras duplicadas.

## Exemplo simples: a embalagem já está pronta, e agora?

1. Confirme que o pedido está **Pago** e já passou para **Em preparação**.
2. Confira se o cliente escolheu uma entrega automática que permite emissão pelo SABA.
3. Gere a etiqueta seguindo os campos do painel. Use **NF-e** se houver nota fiscal emitida e a operação exigir; use **declaração de conteúdo** somente se legalmente permitida.
4. Se a etiqueta aparecer como disponível, **imprima**, cole na embalagem e confira se código de barras e endereços estão legíveis.
5. Leve a encomenda ao local de postagem indicado pelo serviço. Guarde o recibo físico/digital.
6. Confira se o acompanhamento atualizou. Se estiver parado, consulte o histórico e a ação **Atualizar status**; não marque entregue sem confirmação.

### Diferença entre cada etapa

- **Cotação:** descobrir preço e prazo de entrega; não compra frete.
- **Compra/geração de etiqueta:** contrata o serviço de transporte, conforme o provedor.
- **Impressão:** transforma o documento em papel/etiqueta; não comprova postagem.
- **Postagem:** transportadora recebe o pacote.
- **Entrega:** destinatário recebe o pacote.

Não é possível pular etapas só porque o comprador tem urgência.
