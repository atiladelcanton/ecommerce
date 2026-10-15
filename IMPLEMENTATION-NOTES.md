# Implementação 2026-10-12

## Incluído

- Consulta de CEP via contrato `PostalCodeLookup` + ViaCEP, cache e tratamento de indisponibilidade (já iniciada no projeto e revisada).
- Pagamento e frete resolvidos por configuração (`config/payments.php` e `config/shipping.php`) através dos contracts existentes.
- Área do cliente com guard separado do Filament.
- Cadastro, login e logout de clientes.
- Vinculação segura de pedidos visitantes feitos na mesma sessão ao criar conta com o mesmo e-mail.
- Pedidos feitos autenticado recebem `customer_id`.
- Dashboard do cliente e histórico paginado de pedidos.
- Tela interativa de acompanhamento com Livewire (`wire:poll.15s`), timeline, status, pagamento, itens, endereço e rastreio quando disponível.
- Status de pedido: recebido, pago, em preparação, enviado/pronto para retirada, entregue e cancelado.
- Campos opcionais de rastreio no pedido.
- Link de conta no header e CTA para criar conta após checkout visitante.

## Validações executadas neste ambiente

- `php -l` em todos os arquivos PHP: OK.
- `node --test tests/JavaScript/*.mjs`: 14 testes, 14 aprovados.

A suíte PHPUnit/Laravel e o build Vite não puderam ser executados neste ambiente porque o ZIP não contém `vendor/`/`node_modules` completos e o ambiente de execução não tem acesso à internet para restaurar as dependências. Na máquina do projeto, execute:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan test
./vendor/bin/sail npm run build
./vendor/bin/sail pint
```
## Correção: vínculo de pedido visitante ao criar conta

O vínculo não depende mais diretamente do ID interno da sessão PHP, que pode ser regenerado entre requests. Ao concluir um pedido visitante, o sistema guarda na sessão uma capacidade one-shot composta pelo ID do pedido e seu `checkout_token`. No cadastro, somente pedidos presentes nessa sessão, sem cliente e com o mesmo e-mail podem ser vinculados. A capacidade é descartada após a tentativa de vínculo.


## Ajustes de conta, endereços e checkout — 2026-10-12

- Máscaras reutilizáveis para telefone, CPF/CNPJ e CEP no storefront, mantendo dados normalizados no banco.
- `Customer.document` permanece imutável na área do cliente e continua único.
- Vínculo de pedido visitante reforçado: exige capacidade da sessão + mesmo e-mail normalizado + mesmo CPF/CNPJ normalizado; suporta múltiplos pedidos elegíveis.
- Novo `customer_addresses` com regra de endereço principal:
  - primeiro endereço vira principal;
  - apenas um principal por cliente;
  - trocar principal desmarca o anterior;
  - excluir o principal promove outro endereço existente.
- Área do cliente ganhou:
  - Meus endereços;
  - criação/edição/remoção;
  - tornar principal;
  - consulta de CEP reaproveitando `PostalCodeLookup`/ViaCEP;
  - Meus dados;
  - alteração de nome, e-mail e telefone;
  - CPF/CNPJ somente leitura;
  - alteração de senha com validação da senha atual.
- Checkout autenticado:
  - carrega identidade da conta;
  - seleciona automaticamente endereço principal;
  - permite alternar entre endereços salvos;
  - permite usar endereço novo;
  - pode salvar endereço novo na conta e defini-lo como principal;
  - `OrderAddress` continua sendo snapshot independente dos endereços salvos.
- Toasts de sucesso também podem ser iniciados a partir de flash session.

### Testes adicionados

- `tests/Feature/Customer/CustomerAddressAndProfileTest.php`
- novos cenários em `tests/Feature/Customer/CustomerAccountTest.php`
- `tests/Unit/Support/BrazilianDataTest.php`
- `tests/JavaScript/brazilian-data-masks.test.mjs`

### Validações executadas nesta entrega

- `php -l` em todos os PHPs de `app`, `database`, `routes` e `tests`: OK.
- `node --check` nos módulos JS alterados: OK.
- `node --test tests/JavaScript/*.mjs`: 17 testes, 17 aprovados.

A suíte PHPUnit/Laravel e o build Vite não puderam ser executados aqui porque o projeto enviado não inclui `vendor/` nem `node_modules`, e o ambiente de geração não possui Composer/dependências restauradas. Rode no Sail:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan test
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
./vendor/bin/sail pint
```


## Admin operacional e dashboard comercial — 2026-10-12

- Botão **Ver loja** no topbar do Filament.
- Fluxo operacional de pedidos: recebido → pago → preparação → enviado/pronto para retirada → entregue.
- Pagamento aprovado move o pedido automaticamente para **Pedido pago**.
- `TransitionOrderStatusAction` centraliza e valida todas as transições.
- Envios normais exigem transportadora + código de rastreio; retirada usa um fluxo próprio.
- `OrderResource` com listagem, filtros, busca e detalhe completo.
- `PedidosKanban` com colunas operacionais, filtros, polling e modal de rastreio.
- Dashboard administrativo com KPIs, gráfico mensal, gráfico por status, pedidos que precisam de ação e ranking dos produtos mais vendidos.
- O módulo **Fluxo de Caixa** foi removido do MVP. Pedidos e Payments continuam como fonte para faturamento, ticket médio e demais indicadores comerciais.
- A migration `2026_10_12_000004_remove_cash_flow_module.php` remove `cash_flow_entries` em instalações que já haviam aplicado a versão anterior.
- Os gráficos usam `ChartWidget` nativo do Filament para evitar uma nova dependência obrigatória.

### Validações desta entrega

- `php -l` em todos os PHPs: OK.
- `node --test tests/JavaScript/*.test.mjs`: 17 aprovados, 0 falhas.
- `npm run build` não foi concluído porque `node_modules` não está distribuído no ZIP e o ambiente não conseguiu restaurar pacotes externos.
- PHPUnit/Laravel não foi executado aqui pela ausência de `vendor/`. Os novos testes foram adicionados para rodar no Sail.

## 2026-10-12 — Meios de Entrega / Melhor Envio

- Adicionado domínio logístico abstrato com registry de múltiplos providers.
- Implementado Melhor Envio via OAuth 2.0: cotação, compra, geração/impressão, rastreio, cancelamento e webhook assinado.
- Adicionada página Filament **Meios de Entrega** com Melhor Envio + retirada local.
- Adicionado `READY_FOR_SHIPPING` ao workflow do pedido. Para Melhor Envio, `SHIPPED` vem da confirmação do provider/webhook, e não de drag manual.
- `shipments` e `shipment_events` mantêm estado logístico e idempotência separados de `orders`.
- SuperFrete/Correios estão previstos pelos contratos/registry, mas não são marcados como funcionais sem adapter real.
- PHPUnit/Vite ainda precisam ser executados no ambiente Sail que possua `vendor/` e `node_modules`.
