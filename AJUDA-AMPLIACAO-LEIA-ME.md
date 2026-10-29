# SABA — Ajuda 2.0 (continuação)

Atualização incremental sobre a versão que **já recebeu** o pacote `saba-ajuda-atualizacao.zip`.

## Novidades

- **Ajuda desta tela** no topo do Filament, junto a **Ver loja**, abre automaticamente o guia correspondente para Produtos, Categorias, Banners, Cupons, Blog, Clientes, Pedidos, Configurações, Frete, Pagamentos e IA.
- Índice clicável dos capítulos de cada tutorial, com links para seções.
- Próximo/anterior para navegar pelo manual sem voltar ao menu.
- Atalhos para link permanente do artigo e **Imprimir / Salvar PDF** pelo navegador.
- Cinco novos guias: **Acessos e segurança**, **Pós-venda e estornos**, **SEO e divulgação**, **Situações práticas** e **Glossário**.
- Busca inclui o conteúdo dos novos artigos (27 tutoriais no total).

## Instalação

1. Faça backup da pasta do projeto.
2. Extraia **`saba-ajuda-v2-atualizacao.zip`** na raiz do projeto Laravel, mantendo as subpastas.
3. Execute:

```bash
php artisan optimize:clear
```

**Não** há migrations, novas dependências, alterações de Models/Actions ou necessidade de `npm run build`. As novas regras de estilo do manual ficam em `public/help/admin-help.css`.

## Teste manual

1. Entre em `/admin/products`, clique **Ajuda desta tela** e confira o guia de produtos.
2. Abra `/admin/orders`, clique **Ajuda desta tela** e confira o guia de pedidos.
3. Em `/admin/ajuda`, abra **Situações práticas**, clique em uma seção do índice e depois em **Imprimir / Salvar PDF**.
4. Pesquise `logística reversa`, `Groq`, `cupom`, `etiqueta` e `preço promocional`.
5. Confira em tela pequena o topo do painel e a navegação do manual.

**Atenção de segurança identificada na revisão:** `app/Models/User.php::canAccessPanel()` confere somente se o painel se chama `admin`. Este pacote **não muda** a política de autenticação. Antes de conceder acesso a colaboradores, é recomendável implementar controle de permissões por perfil e conferir como os usuários são criados. Essa correção exige uma alteração de autorização separada do módulo Ajuda.
