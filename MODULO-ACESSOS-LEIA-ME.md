# Módulo de usuários e grupos — SABA Ecommerce

Esta atualização usa a versão **SABA com Ajuda corrigida**. Inclui **Administrador** e **Operacional** previamente cadastrados, criação de grupos adicionais e gestão de usuários do Filament.

## Instalação — uma única vez

1. Faça **backup do banco de dados** e dos arquivos do projeto.
2. Extraia `saba-acessos-atualizacao.zip` sobre a raiz do Laravel, preservando `.env`.
3. Execute `php artisan migrate --force` para criar `admin_groups`, adicionar `users.admin_group_id` e criar os grupos padrão.
4. Execute `php artisan optimize:clear`.
5. Acesse o painel com a conta administrativa que já existia e abra **Acessos → Usuários administrativos** e **Acessos → Grupos de acesso**.

**A migração mantém a capacidade de acesso dos usuários administrativos que já existiam**, atribuindo a eles Administrador. Revise esses usuários e altere para Operacional ou outro grupo conforme necessário. Novas contas recebem acesso somente quando vinculadas a um grupo.

## Grupos padrão

- **Administrador:** tudo, inclusive configurações, pagamentos, frete, IA, usuários e grupos. Não editável/excluível pelo painel.
- **Operacional:** categorias, produtos, banners, blog, cupons, clientes, pedidos (inclusive Kanban, etiquetas e status) e financeiro. Não pode ver ou mudar qualquer configuração nem administrar contas. Não editável/excluível pelo painel.
- **Grupos personalizados:** marque os módulos autorizados no formulário de novo grupo. O grupo pode permitir ou negar os módulos individualmente.

Cada usuário pertence a um grupo. Dashboard e Ajuda ficam disponíveis a todos os usuários com grupo válido. Não foram criadas permissões por botão dentro de um módulo.

## Recuperação de acesso

Se o primeiro usuário for criado **depois** da migration e ainda estiver sem grupo, um operador do servidor pode executar:

```bash
php artisan saba:grant-admin email@dominio.com
```

O e-mail deve pertencer a um usuário já existente na tabela `users`. Não há configuração de acesso de administradores no frontend público.

## Proteções

- Proteção de recursos, páginas e operações administrativas no servidor, não apenas remoção dos links do menu.
- Operacional pode imprimir etiquetas e fazer transições de pedido, mas não pode configurar Melhor Envio.
- Administradores não podem remover o próprio grupo de administrador nem rebaixar o último administrador pela interface.
- Grupos padrão são imutáveis pela interface; a exclusão de usuário foi desabilitada para preservar histórico.
- Para bloquear acesso de um ex-colaborador, crie o grupo personalizado `Sem acesso`, sem permissões, e vincule a conta a ele.

## Homologação recomendada

1. Entre como Administrador; confirme acesso a configurações, usuários e grupos.
2. Crie um usuário Operacional; confirme acesso a produtos, banners, pedidos e etiquetas.
3. Confirme que `/admin/store-settings`, `/admin/payment-methods`, `/admin/shipping-methods`, `/admin/ai-settings`, `/admin/admin-users` e `/admin/admin-groups` respondem com acesso negado para Operacional.
4. Crie grupo `Marketing` apenas com Banners e Blog; valide que Produtos e Pedidos são negados.
5. Verifique operações de pedido e impressão de etiqueta como Operacional em homologação (não na produção sem necessidade).

Não exige Node.js, Redis ou alterações em integrações de pagamento e frete.
