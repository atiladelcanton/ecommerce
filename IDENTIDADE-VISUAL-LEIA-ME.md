# SABA — Personalização visual por instalação

## Configuração pelo lojista

No painel **Configurações → Configurações da loja → Identidade visual**:

- Logotipo: upload JPG, PNG ou WebP até 5 MB, salvo no disco público em `store/branding/`. Caso vazio, utiliza a logo SABA já existente no tema.
- Cor primária: destaques, links, badges e cores de navegação.
- Cor secundária: faixas superiores / avisos.
- Cor dos botões: CTAs principais com texto de contraste calculado.
- Os três campos de cor são opcionais e aceitam apenas HEX de seis dígitos (`#RRGGBB`). Vazios preservam as cores nativas do template.
- O logotipo aparece no cabeçalho, no rodapé, no favicon e como imagem social padrão da loja.
- A loja reflete as alterações na próxima requisição após salvar. Não precisa compilar Vite nem executar Node.js.

## Limites

A personalização se aplica somente ao **site público**, nos templates 1 e 2. O visual do administrativo continua no design SABA para manter contraste e legibilidade. Nenhuma configuração permite ao lojista trocar o template: o comando `php artisan switch:template --template=1|2` permanece de uso exclusivo do operador técnico.

## Deploy

Copie o conteúdo do ZIP incremental na raiz do Laravel, **sem apagar arquivos existentes**. Em seguida:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan storage:link
```

Se o link `public/storage` já existir, não precisa recriá-lo. Imagens enviadas pelo admin são arquivos da instalação e devem ser preservadas nos próximos deploys. Não versionar `storage/`. O pacote não altera `composer.json`, `.env`, `.htaccess` ou `public/build`.

**Observação:** faça backup do banco antes das migrations. A atualização adiciona somente quatro colunas nullable em `store_settings`. O PHP e o banco existentes permanecem compatíveis.

## Teste manual sugerido

1. Teste a loja em template 1 sem configurações: visual atual inalterado.
2. Envie uma imagem com fundo transparente e salve; verifique cabeçalho, rodapé, favicon.
3. Aplique cores distintas para primária, secundária e botões; confira a página inicial, vitrine, produto, carrinho, checkout e blog.
4. Repita no template 2 sem mudar o cadastro.
5. Limpe campos de cor e remova a logo; confira retorno ao padrão.
