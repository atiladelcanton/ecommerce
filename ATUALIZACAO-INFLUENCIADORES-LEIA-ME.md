# E-commerce — módulo de influenciadores (v1 integrada)

## Escolha de arquitetura

Foi analisado o `influencers.zip` fornecido pelo usuário (sistema Laravel Orvox + Nuvemshop). Em vez de manter **dois bancos de pedidos** e sincronizar pedidos/cupom por API, esta versão usa um **portal próprio `/influencers` dentro do mesmo Laravel**, com layout e terminologia inspirados no antigo projeto. O ZIP do sistema antigo **não foi modificado**, e os dados históricos da Nuvemshop **não são importados**.

## Instalação (incremental)

1. Faça backup **completo do banco** e do código.
2. Extraia `ecommerce-influenciadores-atualizacao.zip` na raiz do projeto, mantendo diretórios; não substitua `.env` ou arquivos do Composer (eles não estão no pacote).
3. Execute `php artisan migrate --force`.
4. Execute `php artisan optimize:clear`.
5. Acesse **Vendas → Influenciadores**, crie um perfil de teste (nome, e-mail, senha e percentual de comissão).
6. Acesse **Vendas → Cupons de desconto**, crie um cupom com o campo **Influenciador** definido.
7. Faça login de teste em `/influencers/login` e confira a visualização do cupom.
8. Homologue um pedido utilizando o cupom; confirme que está pendente até aprovação do pagamento no provedor.
9. Em ambiente de homologação com Asaas Sandbox, confirme o pagamento via webhook e confira que a comissão passou a **Prevista**.
10. No administrativo, **Comissões de influenciadores**, aprove e registre um pagamento **somente depois de realizar o Pix externamente**.
11. Confira os artigos em **Ajuda → Influenciadores, cupons e comissões** e **Ajuda → Cupons**.

Não precisa de npm, Node ou Redis no servidor. O portal utiliza CSS estático em `public/css/influencers.css`.

## Regras de negócio

- O mesmo cupom existente é vinculado a **zero ou um influenciador**. Um influenciador pode ter vários cupons.
- Cupons sem influenciador continuam iguais.
- O cálculo é `max(0, subtotal_cents - discount_cents) × commission_bps / 10000`, com arredondamento ao centavo, **sem frete**.
- Ao criar o pedido, a comissão fica **pending**. Quando o cupom é consumido com pagamento aprovado, passa para **forecast**. O administrativo pode aprovar e depois registrar manualmente o pagamento.
- O sistema não envia Pix nem transfere dinheiro automaticamente. Os valores aprovados não são valores já pagos.
- Após um cupom ser reservado/usado, sua vinculação com influenciador não pode ser modificada. O percentual é congelado por pedido.
- Cancelamento antes do pagamento cancela previsão; cancelamento/estorno após pagamento encaminha comissões não pagas à revisão, não pressupõe reembolso.
- Não há atualização automática de cupons e pedidos da Nuvemshop, importação do histórico, ranking ou gamificação nesta etapa.
- Não há novas bibliotecas PHP ou JavaScript.

## Atenção a pagamentos e testes

Esta implementação depende das notificações e conciliações do gateway já existente, inclusive dos webhooks Asaas. **Não considere uma venda confirmada somente pelo retorno do navegador.** Antes de vender em produção, teste com pagamentos Sandbox, cancelamentos, estornos e dupla tentativa. As ações de repasse estão disponíveis apenas ao grupo Administrador e são lançamentos manuais.

## Arquivos principais

- `app/Domain/Influencer/Models` — influenciadores e comissões.
- `app/Domain/Influencer/Services` — atribuição e movimentação de comissões.
- `app/Domain/Influencer/Repositories` — relatórios escopados por influenciador.
- `app/Filament/Resources/Influencers` — cadastro administrativo.
- `app/Filament/Resources/InfluencerCommissions` — gestão de comissões.
- `app/Http/Controllers/Influencer`, `resources/views/influencers`, `public/css/influencers.css` — portal privado.
- `resources/help/influenciadores.md`, `config/admin_help.php`, `app/Filament/Support/BundledHelpArticles.php` — tutorial disponível no painel.
- `tests/Feature/Influencer/InfluencerProgramTest.php` — testes de regressão.
