# SABA — Instalação do Mercado Pago e reservas financeiras

Este pacote inclui a implementação do Checkout Pro via Orders API, integrações configuráveis, webhook assinado, consultas periódicas, interface administrativa, pagamentos pendentes, tentativas e a reserva de estoque vinculada ao estado real da cobrança.

## Instalação em um banco existente

**Faça backup do banco e do código antes de implantar. Não execute migrate:fresh. Não substitua o `.env` ou `APP_KEY` da instalação.**

No diretório do projeto, com Sail e as dependências instaladas:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail artisan test
./vendor/bin/sail pint --test
./vendor/bin/sail npm run build
```

Em desenvolvimento, mantenha ativos em terminais distintos:

```bash
./vendor/bin/sail artisan schedule:work
./vendor/bin/sail artisan queue:work --queue=default --tries=6
```

Em produção, cron `php artisan schedule:run` a cada minuto e um serviço permanente `php artisan queue:work`. Certifique-se de que o `APP_URL` está definido para a URL pública HTTPS correta.

## Configuração no administrativo

1. Em **Configurações → Meios de Pagamento**, selecione sandbox e informe o Access Token de testes e o segredo do webhook da aplicação Mercado Pago.
2. Registre no Mercado Pago a URL mostrada no card do SABA, com evento **Order**. Webhook precisa ser acessível em HTTPS.
3. Salve, teste a conexão e ative o ambiente. As credenciais de produção devem ser registradas separadamente; nunca copie um Access Token de produção para sandbox.
4. Faça um pedido de teste e simule aprovação, pagamento recusado e abandono do checkout, verificando webhook e estado dos estoques.
5. Homologue o comportamento real de expiração antes de ativar produção.

## Comportamento exato de estoque

- Antes da criação de cobrança, pedidos assistidos mantêm a política original de reserva por 24 horas.
- Após a criação de uma tentativa Mercado Pago, `payment_hold=1` mantém produtos reservados mesmo depois das 24 horas originais. A reserva **não diminui o estoque físico**.
- Enquanto o provedor indica uma cobrança ainda pendente ou não é possível confirmar seu estado (ex.: falha de rede), a reserva permanece. O **prazo estimado local não basta** para liberar produtos.
- Somente cobrança definitivamente reprovada ou cancelada/expirada **confirmada por consulta autenticada** libera a reserva. Cliente pode iniciar nova tentativa, com revalidação do estoque.
- Cobrança aprovada consome reserva e diminui o estoque físico, idempotentemente.
- Pagamento aprovado após liberação anterior, divergências financeiras e tentativas duplicadas são marcados para revisão administrativa; não são silenciosamente enviados ou ignorados.

## Pendências de homologação

Este pacote **não contém credenciais** nem executou cobrança real. Reembolsos e cancelamentos financeiros diretamente pela API continuam sujeitos à operação manual no Mercado Pago; a loja reconhece reembolsos remotos e solicita revisão. A suíte PHP/Laravel **deve ser executada na instalação com `vendor/`** — o ZIP de origem não continha essas dependências, e elas não foram reinstaladas neste ambiente.

Leia também [`docs/payments.md`](docs/payments.md) para detalhes arquiteturais e endpoints.
