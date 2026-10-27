# Variações e estoque

O cadastro do produto contém uma seção opcional de Variações. Produto vendido de uma única forma não precisa de nenhuma variação — isso vale igualmente para máquina, tinta, nylon, rolo, peça ou acessório. Quando houver escolhas, cada linha representa uma combinação completa, por exemplo Cor=Preta + Embalagem=900 ml, Gramatura=120 + Largura=1,20 m ou Tamanho=40x50. Todas as combinações ativas usam as mesmas características; a identidade canônica ignora ordem e caixa. Os seletores da vitrine têm ordem estável, independente da ordenação JSON do MySQL.

Na interface, a variação mostra primeiro apenas as características, disponibilidade e SKU opcional. Preço, promoção, estoque e medidas próprias ficam em um bloco avançado recolhido. Esses valores continuam opcionais: preço vazio herda o produto, estoque vazio compartilha o estoque geral e medidas vazias usam a embalagem geral. Promoção vazia herda a promoção geral somente quando também herda o preço; preço próprio não recebe automaticamente desconto do produto. Promoção própria precisa ser positiva e menor que o preço efetivo. Peso e três dimensões devem ser preenchidos juntos para substituir a embalagem geral.

`product_variants` é reaproveitada. As cores existentes mantêm IDs, nome/valor, estoque e preços herdados; não são recriadas. A remoção no formulário desativa o registro. Combinações reservadas não podem ser removidas ou redefinidas. A seleção é obrigatória quando há variações cadastradas. Combinações inexistentes, inativas ou sem estoque não são compráveis.

`ResolveProductOfferAction` centraliza preço, promoção, SKU, características e medidas. Carrinho, cotação e snapshots de pedido usam a oferta selecionada. Vitrine e filtros usam o menor preço ativo disponível; a página e o link de WhatsApp acompanham a seleção. Pedidos guardam características e medidas contratadas e não mudam quando o catálogo é editado. Nenhum preço de custo é selecionado na consulta pública.

## Estoque físico e reservas

Quantidades são inteiras (unidade, lata, pacote ou rolo fechado). Produtos simples usam o estoque geral. Variação com saldo próprio usa esse saldo; estoque da variação vazio compartilha o saldo geral, sem multiplicá-lo entre cores. Ambos vazios significam sem controle. Produtos sob encomenda não limitam a compra nem reservam/baixam estoque imediato.

`inventory_reservations` tem uma linha única por item de pedido. Reservar não reduz estoque físico: disponibilidade é saldo menos reservas não vencidas. Pedidos assistidos reservam por 24 horas, sem renovação por navegação ou edição do frete. O vencimento é considerado nas consultas mesmo sem o scheduler. O comando abaixo registra a liberação das reservas vencidas:

```bash
./vendor/bin/sail artisan saba:expire-stock-reservations
```

O scheduler agenda o comando a cada minuto. Localmente, manter `./vendor/bin/sail artisan schedule:work` em execução; em produção, configurar o cron padrão do Laravel para `php artisan schedule:run` a cada minuto.

No pagamento de demonstração, consumir reserva, baixar estoque, registrar pagamento e aprovar pedido ocorre na mesma transação, com locks e idempotência por item. O checkout automático usa as mesmas Actions. Após expiração, a conclusão revalida e consome somente se houver saldo disponível. Se o gateway falhar, a transação reverte a baixa. Antes de substituir o gateway fake por cobrança assíncrona real, implementar o ciclo de reserva/expiração adequado ao gateway nessa etapa própria.

Cancelamento comercial libera reservas e repõe uma única vez o que foi baixado antes do envio. Cancelamento de etiqueta não altera estoque comercial. A reposição não presume reembolso financeiro. Ações de catálogo bloqueiam saldo inferior às reservas e exclusão de produto reservado. Pedidos históricos sem registros de reserva não recebem baixa retroativa.

## Atualização e reversão

Aplicar migrations incrementais normalmente, sem migrate:fresh no banco da loja. O backfill de características não altera os preços existentes. A reversão que tornaria frete e total novamente obrigatórios recusa pedidos com valores ainda indefinidos, evitando inventar frete grátis durante um rollback. Antes de reverter, concluir a negociação desses pedidos ou tratar sua migração explicitamente. Os testes de rollback usam dados históricos com totais definidos.

## Seleção de variações na loja — 26/10/2026

A página pública não usa mais selects para configurar produtos. Quando a variação tem uma ou duas características, cada valor aparece como botão direto (com amostra visual para `Cor`). A seleção de uma característica filtra as possibilidades da seguinte; alterar uma escolha limpa escolhas posteriores. Valores cujas combinações não possuem estoque disponível permanecem visíveis, mas desativados. A seleção é validada no servidor.

Quando as combinações usam três ou mais características (por exemplo, material, fios, cor, largura e comprimento), a loja mostra **cada combinação vendável como opção completa selecionável em um clique**; preço e disponibilidade constam da opção. Listas acima de oito combinações oferecem expansão para exibir todas, sem remover as outras do HTML. Essa distinção é exclusivamente de apresentação, gerada por `GetProductPurchaseDataAction`; não altera a modelagem das variações, nem IDs, snapshots ou regras comerciais.

Tanto os botões simples quanto as opções completas atualizam a oferta e o identificador da variação selecionada, utilizados pelo carrinho e pelo link de cotação por WhatsApp. `ResolveProductOfferAction`, `InventoryAvailability` e a validação existente do carrinho continuam sendo a fonte de verdade de preços e estoque.
