## O que é um banner?

É uma imagem de destaque da **página inicial**, normalmente apresentada no carrossel principal. Pode anunciar campanhas, categorias ou produtos. Um banner não cria desconto automaticamente; se anunciar uma promoção, o produto correspondente também precisa ter preço correto.

## Tamanhos e formatos das imagens

O formulário **não obriga uma dimensão exata em pixels**: ele aceita JPG, PNG ou WebP, com **até 5 MB por arquivo**. Entretanto, para evitar cortes e distorções, use estas **recomendações de design** e confira o resultado em cada template:

| Uso | Referência recomendada | Observação |
| --- | --- | --- |
| Desktop | **1600 × 600 px** (proporção 8:3) | É a proporção indicada pelo componente de banner atual; ideal para uma arte horizontal. |
| Celular | **750 × 900 px** como ponto de partida | Não é um requisito técnico; teste no celular real. Se houver cortes, ajuste conforme o template. |
| Formato | WebP ou JPG para fotos; PNG para transparências | Comprima para manter carregamento rápido. |

O site utiliza `object-contain` no banner original, portanto diferenças de proporção podem gerar **espaços vazios**, em vez de cortar a arte. O template alternativo pode ter apresentação diferente. Evite escrever informações muito importantes junto às bordas, botões do carrossel ou áreas com pouco contraste.

## Como cadastrar

1. Vá a **[Configurações → Banners](/admin/banners)** e escolha **Novo banner**.
2. Dê um **Título** interno que permita localizar a campanha mais tarde.
3. Em **Link**, cole a URL completa de destino (`https://...`) se quiser um banner clicável. Teste esse endereço antes de salvar.
4. Preencha **Texto alternativo** descrevendo a imagem para acessibilidade. Exemplo: “Promoção de tintas para serigrafia”.
5. Em **Imagem desktop**, envie a arte horizontal (obrigatória).
6. Em **Imagem mobile**, envie a arte vertical quando quiser uma composição otimizada para celulares (opcional).
7. Marque **Ativo** para permitir a exibição.
8. Defina **Ordem** (menores números têm prioridade).
9. Opcionalmente, preencha **Início** e **Fim** para agendar a campanha. O intervalo inclui as datas/horários limites, e a data final não pode ser anterior à inicial.
10. Salve e confirme a aparência no computador e no celular.

## Campanha agendada: exemplo

- Título: “Semana de ofertas”.
- Imagem desktop: 1600×600 px.
- Imagem mobile: arte adaptada e legível para celular.
- Início: 01/11 às 08:00.
- Fim: 07/11 às 23:59.
- Ativo: sim.

Fora desse intervalo, o banner não será exibido publicamente mesmo que continue no banco.

## Problemas frequentes

- **Não aparece:** veja se está ativo, se a data de início já chegou, se não expirou e se a imagem foi salva.
- **Aparece em branco:** verifique o formato do arquivo, existência do link de armazenamento público e o resultado no navegador.
- **Texto pequeno no celular:** use uma arte mobile própria; não dependa da redução da arte de desktop.
- **Banner abre destino errado:** revise o campo Link e teste a URL.
- **Lentidão:** compacte a imagem; não é necessário usar 10 MB para uma arte de banner (o limite do formulário é 5 MB).

## Exemplo pronto de campanha na página inicial

Imagine um banner que anuncia um produto com desconto durante uma semana.

1. No produto, cadastre e salve o **preço promocional correto**. O banner sozinho não reduz preço.
2. Prepare uma imagem horizontal de **1600 × 600 px** para computador e, se possível, uma arte própria para celular. Essa é uma sugestão de criação, **não uma obrigação do formulário**.
3. Abra **Banners → Novo banner** e preencha o título interno, como “Semana da Serigrafia — novembro”.
4. Na URL do link, cole o endereço da página real do produto ou da categoria.
5. Envie a imagem, marque como ativo e escolha o período da campanha.
6. Salve. Se a data de início já tiver chegado, confira a home. Se for uma campanha futura, confira quando entrar em vigor.

### Antes de divulgar

- Abra o site no celular e no computador: a arte permanece legível?
- Clique no banner: ele leva ao produto ou página anunciada?
- Os preços e o período promocional estão de acordo com o anúncio?
- A imagem contém informações escondidas em bordas ou cortadas?

**Se precisar de uma peça nova:** não edite a arte antiga sem guardar uma cópia do arquivo. O ideal é manter os banners por campanha, ativando e desativando conforme necessário.
