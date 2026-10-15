# Validação de Meios de Entrega

## Frete assistido e variações — 15/10/2026

Esta revisão mantém o gateway de demonstração e adiciona a conclusão de pedidos com frete negociado, variações combinadas e reserva de estoque. O histórico abaixo descreve a entrega anterior.

- Suíte completa no Sail/SQLite: **382 aprovados, 3 ignorados, 1917 assertions**. Os três ignorados exigem MySQL/pcntl e são executados separadamente.
- MySQL 8.4 no banco isolado `saba_shipping_test`: **103 aprovados, 406 assertions**, incluindo os três testes de concorrência ignorados no SQLite.
- `php -l`: **293 arquivos PHP** de aplicação, rotas, migrations e testes sem erros; os últimos testes também foram verificados após sua edição.
- `./vendor/bin/sail pint --test`: **aprovado globalmente** no estado atual do projeto.
- `./vendor/bin/sail npm run build`: **aprovado**.
- **37 classes/traits Filament** carregadas sem erro; **217 classes/interfaces/traits/enums** da aplicação sem duplicidade. Sem `view`/`heading`/`description` estáticas incompatíveis ou método Livewire `transition()`.
- Rotas de revisão/checkout, migrations aplicadas e comando agendado de expiração conferidos. Checkout e Actions de pedido continuam sem acoplamento ao Melhor Envio.
- Testes automatizados bloqueiam HTTP sem fake em `Tests\TestCase`; nenhuma API externa foi chamada por essas suítes. Logs de integração registram contexto técnico sem credenciais; segredos persistidos não são reexibidos no formulário.

### Verificação no navegador

Chromium com SQLite exclusivo de teste e HTTP bloqueado salvo respostas fake: seleção de Poliéster + Tamanho, preço por combinação, carrinho, CEP disparando cotação, checkout automático, solicitação sem frete, publicação administrativa e conclusão explícita pelo link assinado. Dois pedidos pagos fake produziram somente dois pagamentos e baixaram cada combinação correta. Também foram conferidos o formulário administrativo, o botão de copiar link e a seleção móvel em 390 px sem overflow horizontal. Nenhum erro de console permaneceu nos fluxos finais. Os processos temporários de teste foram encerrados.

A cotação automática de teste foi R$ 30,48 para a combinação de R$ 180,00 (total R$ 210,48). No fluxo assistido, o pedido permaneceu sem pagamento e com total **A definir** até a publicação do frete de R$ 48,90. O cliente revisou esse total e confirmou o pagamento fake. Reservas não reduzem estoque físico antes da aprovação e não se renovam ao abrir o link.

O diagnóstico manual separado na conta Sandbox identificou que o cálculo de um único serviço retorna um objeto, formato antes descartado. Um item com medidas aceitas retornou cotações; equipamentos maiores continuam sujeitos aos limites reais da transportadora. Não foram compradas etiquetas reais nesta revisão.

### Migrations desta revisão

1. `2026_10_15_000002_extend_product_variant_combinations.php`: reaproveita as variações, preserva IDs e adiciona características/preços/embalagens e snapshots.
2. `2026_10_15_000003_create_order_shipping_quotes.php`: revisões do frete e totais indefinidos antes da negociação.
3. `2026_10_15_000004_create_inventory_reservations.php`: reserva por item, consumo, liberação e reposição idempotentes.

As três foram aplicadas no banco local por `sail artisan migrate`, sem recriar tabelas ou alterar migrations antigas. Backfill e reversão foram exercitados em banco de teste. A reversão que tornaria totais obrigatórios recusa pedidos ainda sem frete definido; tratar esses pedidos antes de um downgrade.

### Arquivos e operação

- `app/Actions/Catalog/SyncProductVariantsAction.php`, `ResolveProductOfferAction.php`, `ManageOrderInventoryAction.php` e `app/Domain/Catalog/Services/InventoryAvailability.php`: combinações e estoque.
- `app/Filament/Resources/Products/ProductResource.php`, `app/Livewire/Store/ProductPurchase.php`: cadastro e seleção.
- `app/Infrastructure/Shipping/MelhorEnvio/MelhorEnvioShippingProvider.php`, `app/Actions/Shipping/QuoteShippingAction.php`: normalização e isolamento das cotações.
- `app/Actions/Order/CreateInitialOrderAction.php`, `ConfirmQuotedOrderAction.php`, `PayOrderAction.php`, `app/Actions/Shipping/PublishOrderShippingQuoteAction.php`: solicitação, negociação e conclusão.
- `app/Livewire/Store/ShippingQuoteCheckout.php`, `app/Filament/Resources/Orders/Pages/ViewOrder.php`, views e rotas correspondentes: revisão protegida e publicação administrativa.
- `docs/shipping.md`, `docs/product-variants.md`, `docs/database.md`, `docs/business-rules.md`, `docs/roadmap.md`: arquitetura e regras atualizadas.

Em outra instalação, executar `./vendor/bin/sail artisan migrate` e `./vendor/bin/sail npm run build`. Manter `./vendor/bin/sail artisan schedule:work` em desenvolvimento ou o cron Laravel `schedule:run` a cada minuto em produção. O comando `saba:expire-stock-reservations` pode ser executado manualmente. A disponibilidade já ignora reservas vencidas, mesmo sem o scheduler.

Credenciais reais não são necessárias para validar o fluxo de pagamento atual. Cobrança financeira real, compra de etiquetas com saldo e webhooks em produção continuam dependendo da homologação correspondente; esta revisão não implementa um gateway financeiro real.

## Histórico da validação anterior

Validação concluída em 15/10/2026. Nenhuma credencial real de Melhor Envio foi utilizada nos testes. `Tests\TestCase` proíbe chamadas HTTP sem fake.

## Resultado

- `./vendor/bin/sail test`: **344 aprovados, 1 ignorado, 1764 assertions**. O teste ignorado exige MySQL/pcntl e foi executado separadamente abaixo.
- Suíte Shipping em MySQL 8.4, no banco isolado `saba_shipping_test`: **63 aprovados, 241 assertions**, incluindo dois processos concorrentes, migrations reversíveis, snapshots legados e retomada após compra.
- `php -l`: **83 arquivos PHP verificados sem erros**.
- Pint dos arquivos PHP criados/alterados: **aprovado**.
- `./vendor/bin/sail pint --test`: aponta **25 arquivos preexistentes** fora desta alteração, incluindo uma migration antiga. Não foi feita reformatação geral nem edição de migration aplicada para ocultar essa pendência.
- `./vendor/bin/sail npm run build`: **aprovado**.
- Cinco rotas de entrega conferidas; 37 classes/traits Filament carregadas sem incompatibilidade; nenhuma classe de aplicação duplicada detectada.
- Checkout/Actions de pedido sem referência a Melhor Envio ou fallback fake. Nenhuma propriedade `view`, `heading` ou `description` incompatível como static nas classes Filament verificadas.

## Migrations incrementais

1. `2026_10_14_000001_create_shipping_catalog.php`
2. `2026_10_14_000002_add_resumable_shipping_operations.php`
3. `2026_10_14_000003_add_store_sender_registration.php`
4. `2026_10_14_000004_link_legacy_shipping_environments.php`
5. `2026_10_15_000001_create_shipping_agency_carrier.php`

Aplicação: `./vendor/bin/sail artisan migrate`. Não usar `migrate:fresh` no banco da loja. Nos registros legados sem ambiente salvo, a migration de compatibilidade usa o ambiente vigente da integração, sem alterar valores ou nomes históricos; conferir esses vínculos se houve troca de ambiente anteriormente.

## Compatibilidade de testes antigos

As fixtures de checkout optam explicitamente pelo adapter de demonstração; ele não é oferecido na aplicação. O teste de sessão de outro cliente agora limpa os dados da sessão, em vez de somente trocar seu ID. Assertions administrativas consideram os widgets lazy do Filament 5. O recurso legado de fluxo de caixa não abre sem a tabela deliberadamente removida pelo MVP; o teste da Action monetária antiga usa apenas seu schema histórico isolado.

## Homologação ainda necessária

OAuth com a opção de configuração durante instalação desmarcada já foi confirmado pelo usuário. A sincronização manual de unidades na conta Sandbox conectada atualizou 25 unidades dos Correios e 233 da Jadlog, incluindo uma unidade compartilhada. Essa validação manual fica separada dos testes automatizados, que continuam usando Http::fake e bloqueando requisições externas. Ainda precisam de homologação com a conta real: capacidades comerciais de cada serviço, regras de agência/seguro/adicionais, compra com saldo, retorno dos webhooks e formatos de impressão disponíveis no ambiente. Serviços sincronizados começam desabilitados e manuais; habilitar a compra automática somente após confirmar seu suporte comercial. SuperFrete e Correios não possuem API implementada.

## Revisão dos cards e modal — 15/10/2026

- A tela geral contém somente cards compactos de providers. A configuração do Melhor Envio está isolada em um componente administrativo Livewire com quatro abas e salvamento independente. Transportadoras são editadas no mesmo modal. Retirada tem modal próprio.
- Cobertura adicional: gravação completa de transportadora, configurações independentes, rascunhos entre abas, segredo não reexibido, valores padrão operacionais, retirada, filtros normalizados, resposta oficial com `companies`, agência compartilhada, seleção na geração de envio, erros de validação/HTTP e migração com backfill e rollback preservando IDs e defaults.
- Conferência no Chromium headless usando banco SQLite isolado: login real, abertura do modal pelo card, navegação entre abas, edição e gravação de serviço sem reiniciar a aba, acesso às unidades, salvamento operacional, rejeição visual de ativação inválida e viewport de 390px sem overflow horizontal da página. Nenhum provider foi chamado por esse teste de interface.
- Migração incremental de vínculo agência/transportadora aplicada no banco local da loja; a conexão OAuth existente foi preservada.
- Nesta revisão, 13 arquivos PHP passaram em `php -l` e Pint. O build Vite foi regenerado; as cinco rotas foram conferidas e as 37 classes/traits Filament carregaram sem incompatibilidades. O Pint global continua indicando os mesmos 25 arquivos preexistentes.
