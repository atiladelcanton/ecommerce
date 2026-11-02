# Atualização: nome e logotipo da loja no /admin e no site

Esta atualização parte da versão **E-commerce + Influenciadores** e não exige novas tabelas.

## O que muda

- O Filament (`/admin`, inclusive login) passa a usar o **Nome da loja** e o **Logotipo da loja** configurados em **Configurações → Configurações da loja → Identidade/Identidade visual**.
- Se não houver logotipo enviado, o painel e a loja mostram o nome cadastrado como texto, sem usar o logotipo de outra empresa.
- Os cinco temas existentes continuam com seus próprios layouts; todos usam a mesma identidade da loja.
- As páginas Quem Somos, Contato, Blog e Minha conta/Acompanhamento de pedidos usam o nome da loja em lugar de textos fixos da marca original.
- O manual do administrador foi atualizado e sincronizado com sua versão incorporada ao PHP.
- Quando não houver imagem, o campo de favicon e a meta-imagem social são omitidos; não são enviados com URL vazia.

## Instalação do ZIP incremental

1. Faça um backup do código e dos arquivos enviados pela loja.
2. Extraia o ZIP e copie todos os arquivos e pastas para a raiz do projeto Laravel, substituindo os arquivos correspondentes.
3. Na raiz do projeto, execute:

   ```bash
   php artisan optimize:clear
   ```

4. Se o upload do logotipo já está salvo, mas a imagem não abre, verifique se o `public/storage` aponta para `storage/app/public`. Se faltar o link, execute:

   ```bash
   php artisan storage:link
   ```

5. Entre em **/admin/store-settings**, confira o nome e a imagem e clique em **Salvar configurações**.
6. Atualize `/admin`, `/admin/login` (em janela anônima), a página inicial, `/quem-somos`, `/contato` e `/blog` (se ativo).
7. Se visualizar a imagem antiga, faça um recarregamento completo do navegador (Ctrl+Shift+R) e confira a URL da imagem.

**Não execute migrations nem npm build.** Não substitua o `.env` nem remova os arquivos enviados para `storage/app/public`.

## Observações

- Essa atualização não altera regras de pagamento, pedidos, frete, cupons ou influenciadores.
- O nome salvo em `store_settings.store_name` é a referência para os textos do site e do painel. O `APP_NAME` continua sendo apenas o fallback quando a loja ainda não está configurada.
- A autorização para editar a identidade visual continua limitada ao administrador com acesso a Configurações da loja.
