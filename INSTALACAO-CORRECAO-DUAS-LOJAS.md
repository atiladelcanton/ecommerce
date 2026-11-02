# Instalação — Logo pública e diagnóstico de 403

Esta atualização é compatível com a versão de novembro de 2026 do e-commerce.

1. Faça backup do código e do banco das instalações SABA e Lojinha.
2. Extraia o ZIP incremental **na raiz do Laravel de cada loja que utiliza esse código**, preservando as pastas e sobrescrevendo os arquivos indicados. O ZIP não contém `.env`, `vendor`, `storage` nem migrações.
3. Execute `php artisan optimize:clear` separadamente em cada instalação.
4. Execute `php artisan loja:diagnosticar` nas duas pastas. Guarde as informações, mas **não compartilhe o arquivo `.env`**.
5. Leia `DIAGNOSTICO-DUAS-LOJAS.md` para tratar um link `public/storage` que aponta para a instalação antiga e recuperar, com segurança, o grupo administrativo de uma conta que realmente perdeu a autorização.

Não execute `migrate:fresh`, `key:generate` nem exclusões de diretórios de armazenamento como tentativa de correção.

O patch da logo passa a utilizar URLs públicas relativas ao domínio visitado (`/storage/...`), mas isso **não dispensa** a existência correta do link `public/storage` e o document root do servidor.

Esta atualização **não contorna nem altera as regras de autorização do Filament**. O erro 403 depende do usuário/grupo ou da configuração da instalação e deve ser diagnosticado antes de qualquer alteração de dados.

Sem migrações e sem necessidade de compilar o Vite.
