## Quem pode usar o administrativo?

O endereço `/admin` é exclusivo para a equipe da loja. Cada pessoa deve ter seu próprio e-mail e sua própria senha. **Nunca compartilhe contas ou credenciais de serviços externos.**

O painel agora possui **grupos de acesso**. O Administrador decide quais módulos cada pessoa pode abrir e utilizar. As permissões são verificadas também quando alguém tenta acessar uma URL diretamente: esconder o menu não é a única proteção.

## Conheça os dois grupos que vêm prontos

| Grupo | O que pode fazer | O que não pode fazer |
| --- | --- | --- |
| **Administrador** | Acessar e operar todos os módulos, cadastrar usuários e criar grupos | Não há restrições de módulos |
| **Operacional** | Cadastrar categorias, produtos, banners e artigos; gerenciar cupons, clientes, pedidos, etiquetas e financeiro | Não pode abrir Configurações da Loja, Meios de Pagamento, Meios de Entrega, Inteligência Artificial nem Usuários e Grupos |

**Atenção:** o grupo Operacional pode **gerar, cancelar, verificar e imprimir etiquetas dentro de Pedidos**, conforme as regras do envio. Ele **não** pode configurar o Melhor Envio ou mudar credenciais. Também pode usar a IA já configurada nos formulários de produto e blog, mas não mudar a chave Groq.

Os dois grupos padrão são protegidos: não é possível editá-los ou excluí-los pela interface. Assim, as regras acima não mudam por acidente.

## Como cadastrar um novo usuário da equipe

Apenas Administradores ou grupos personalizados autorizados a gerir acessos podem fazer isso.

1. Entre no administrativo com sua própria conta.
2. No menu, abra **Acessos → Usuários administrativos**.
3. Clique em **Novo usuário**.
4. Preencha **Nome**, **E-mail** e **Senha** (use uma senha exclusiva e com pelo menos 10 caracteres).
5. Em **Grupo de acesso**, selecione **Operacional** para um colaborador da operação ou outro grupo adequado.
6. Clique para salvar. Informe ao colaborador o endereço do painel; compartilhe a senha inicial somente por canal seguro.
7. Peça à pessoa que entre e teste as telas necessárias ao trabalho.

**Não escolha Administrador apenas para resolver um problema de acesso.** Esse grupo permite alterar credenciais financeiras e administrar todas as contas do painel.

## Como alterar grupo, nome, e-mail ou senha de um funcionário

1. Abra **Acessos → Usuários administrativos**.
2. Selecione o usuário desejado.
3. Altere o nome, e-mail ou grupo. Para manter a senha atual, **deixe o campo Senha vazio**.
4. Para trocar a senha, digite a nova senha e salve.
5. Peça ao funcionário para entrar novamente e confirmar que as telas corretas aparecem.

O sistema impede que você retire **seu próprio** grupo Administrador e também protege o **último Administrador** da instalação contra rebaixamento. A exclusão de usuários pelo painel está desabilitada nesta versão; quando alguém sair da equipe, atribua a pessoa a um grupo personalizado **sem permissões** para bloquear o acesso e preservar o histórico de operações.

## Como criar um grupo personalizado

Utilize grupos personalizados quando precisar de alguém com acesso limitado, por exemplo, somente catálogo e blog.

1. Vá até **Acessos → Grupos de acesso**.
2. Clique em **Novo grupo**.
3. Informe um nome descritivo, como `Marketing`.
4. Preencha um identificador simples sem espaços, como `marketing`.
5. Em **Permissões por módulo**, marque as áreas necessárias: por exemplo **Banners** e **Blog**.
6. Salve o grupo.
7. Abra o usuário e selecione esse novo grupo no campo **Grupo de acesso**.
8. Faça o teste com o usuário para verificar se ele vê apenas os módulos corretos.

As permissões de um grupo personalizado controlam o **acesso completo a cada módulo** (visualizar, cadastrar e editar, quando o módulo permitir). Não são permissões separadas por botão ou operação. O Dashboard e a Ajuda continuam acessíveis a qualquer conta com grupo válido.

## O que fazer quando alguém sai da equipe

1. Um administrador deve criar (uma única vez) um grupo `Sem acesso` **sem marcar nenhuma permissão** e transferir para esse grupo a conta do colaborador que saiu.
2. Revise também os acessos da pessoa no Asaas, Melhor Envio e Groq, se ela tiver contas externas.
3. Troque credenciais eventualmente compartilhadas e solicite que a pessoa encerre sessões abertas. A mudança de grupo restringe os módulos nas próximas requisições; não substitui a revogação de chaves externas.
4. Caso exista suspeita de uso indevido, preserve registros e procure suporte técnico.

## Cuidados com Asaas, Melhor Envio e Groq

| Credencial | Para que serve | Cuidados |
| --- | --- | --- |
| Senha do `/admin` | Entrar no painel | Individual e nunca compartilhada |
| Chave Asaas | Gerenciar cobranças e pagamentos | Somente pessoas autorizadas a configurações financeiras |
| Token Melhor Envio | Cotações e logística | Não divulgar em mensagens ou imagens |
| Chave Groq | Gerações por IA | Proteja contra consumo indevido |
| Segredo de webhook | Autenticar notificações das plataformas | Uso técnico, nunca exibido publicamente |

## Perguntas frequentes

**Um Operacional pode imprimir uma etiqueta?** Sim, dentro do pedido. Não precisa entrar em Configurações → Meios de Entrega.

**O Operacional pode usar o botão de IA no produto?** Sim, se a IA já estiver habilitada e configurada por um Administrador. Não pode mudar o modelo, chave ou limite da IA.

**Quero um funcionário apenas para cadastrar produtos.** Crie um grupo personalizado que tenha apenas **Catálogo — Produtos**. Se ele precisar criar categorias, libere também **Catálogo — Categorias**.

**Quero permitir acesso ao financeiro, mas não às contas de pagamento.** Libere **Financeiro — Fluxo de caixa** e não libere **Configurações — Meios de pagamento**.

**Não consigo abrir uma página.** Confirme com um Administrador se o seu grupo inclui aquele módulo. Se a mensagem for **403 — Acesso negado**, não tente alterar a URL manualmente.

**Criei o primeiro usuário pelo terminal e não consigo entrar.** O responsável técnico deve conceder o grupo Administrador usando o comando `php artisan saba:grant-admin usuario@exemplo.com`. Esse comando deve ser executado exclusivamente no servidor por quem possui acesso técnico.
