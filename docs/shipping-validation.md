# Validação de Meios de Entrega

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
