# Hotfix Asaas — campos obrigatórios de endereço no Checkout

## Problema

`POST /v3/checkouts` retornava `HTTP 400 invalid_object` ao receber `customerData` sem `address`, `addressNumber`, `postalCode` e `province`. A aplicação possuía esses dados no snapshot de `order_addresses`, mas o adapter não os encaminhava.

## Correção

- `PayOrderAction` carrega o endereço imutável do pedido e o fornece ao `PaymentRequest` em `buyerAddress` (nomes independentes do provider).
- `AsaasGateway` mapeia `street` → `address`, `number` → `addressNumber`, `postal_code` → `postalCode`, `neighborhood` → `province`. `complement` é opcional.
- Não envia `city` porque Asaas espera o código IBGE, enquanto o SABA armazena o nome textual da cidade. Não inventar conversões.
- Caso o snapshot esteja incompleto, bloqueia a chamada remota com uma validação amigável e não cria cobrança inválida.
- Logs de rejeição do Asaas incluem apenas códigos de erro e nomes dos campos rejeitados, sem payload, endereços, nomes, documentos ou tokens.

## Aplicação

Copie os arquivos deste ZIP na raiz do projeto preservando os caminhos. Faça backup antes. **Não há migrations novas**, nem mudanças de variáveis `.env` ou webhook.

```bash
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail artisan test --filter=AsaasCheckoutTest
./vendor/bin/sail artisan test
./vendor/bin/sail pint --test
```

Depois, reabra o pedido pendente e tente novamente pelo fluxo normal da loja. Se a tentativa rejeitada não possui uma cobrança remota ativa, o fluxo existente poderá criar uma nova tentativa; nunca repita manualmente `POST /checkouts` sem verificar o estado anterior.

## Referências

- https://docs.asaas.com/docs/como-informar-os-dados-do-cliente
- https://docs.asaas.com/reference/criar-novo-checkout

**Estado da validação:** sintaxe e integridade do ZIP verificadas neste ambiente. PHPUnit precisa ser executado no Sail com `vendor/` instalado. O sucesso de uma cobrança real de homologação ainda depende da conta Sandbox Asaas.
