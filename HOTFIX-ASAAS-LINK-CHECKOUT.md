# Hotfix SABA — URL do Checkout Asaas (21/10/2026)

## Causa

O endpoint `POST /v3/checkouts` retornou HTTP 200 e um `link` válido no ambiente Sandbox:

`https://sandbox.asaas.com/000/checkoutSession/show/<checkout-id>`

O adapter anterior ignorava `link` e montava `https://asaas.com/checkoutSession/show?id=<checkout-id>`. A URL antiga abriu a tela de **Página não encontrada**.

O Asaas publicou em 25/09/2026 uma mudança gradual das URLs públicas, começando em 28/09/2026, recomendando usar URLs completas retornadas pela API: https://docs.asaas.com/changelog/altera%C3%A7%C3%A3o-na-estrutura-de-urls-p%C3%BAblicas-do-asaas

## Alterações

- Usa `response.link` exatamente como o Asaas retornou em `AsaasGateway::createPayment`.
- Valida HTTPS, domínio oficial e o ambiente (Sandbox vs. produção); não depende de segmentos fixos de rota.
- Resposta sem `link` válido é tratada como inconclusiva: **não repete o POST** para não duplicar cobrança.
- Webhook antecipado não inventa URL; aguarda a resposta original da criação.
- Checkout, link de pagamento do pedido, tela do cliente, frete assistido e retomada conferem ambiente e URL válida.
- Testes atualizados: preservação do link real, rejeição de domínio/ambiente inválido e webhook antecipado.

## Instalação

1. Faça backup do projeto.
2. Extraia o ZIP de arquivos alterados na raiz do projeto.
3. Execute `./vendor/bin/sail artisan optimize:clear`.
4. Execute `./vendor/bin/sail artisan test --filter=AsaasCheckoutTest` e depois a suíte completa.
5. Realize novo checkout Sandbox e verifique que a URL começa com `https://sandbox.asaas.com/`.

Não há migrations nem alteração de credenciais.

## Sobre o checkout SABA-000013 já criado

O hotfix resolve as **novas cobranças**, mas a URL errada salva no pagamento antigo não é substituída por mágica. Abra o `link` original que já veio na resposta do Asaas, enquanto a cobrança estiver `ACTIVE`. Não crie outra cobrança para o mesmo pedido apenas para trocar o link.

Se precisar corrigir a URL desse pagamento no banco, use um procedimento autorizado e transacional que valide o `external_id`, o ambiente `sandbox`, o estado `pending` e que o link corresponde exatamente ao retornado pela API. Não modifique `status`, `amount_cents` ou a reserva de estoque para consertar um link.

**Atenção:** o checkout do exemplo foi criado com expiração em 60 minutos. Se já estiver `EXPIRED`, aguarde o webhook confirmar expiração e liberar a reserva antes de gerar outro checkout, conforme fluxo existente.

## Verificações locais realizadas neste pacote

Lint PHP dos arquivos alterados, verificação de integridade ZIP e testes JavaScript. PHPUnit precisa ser executado com `vendor/` disponível no Sail.
