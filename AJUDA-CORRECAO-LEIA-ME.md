# SABA — Correção definitiva dos artigos de Ajuda

## O que causou a falha

A atualização Ajuda 2.0 anterior trouxe somente 5 arquivos `resources/help/*.md`, embora o menu referencie 27 guias. A página mostrava `Artigo temporariamente indisponível` quando o arquivo Markdown não estava presente.

## Como instalar

1. Faça backup do projeto instalado.
2. Extraia o **ZIP incremental desta correção** e copie **todas as pastas e arquivos** para a raiz do seu projeto Laravel, mantendo a estrutura de diretórios. Substitua arquivos existentes.
3. No terminal, na raiz do Laravel, execute:

```bash
php artisan optimize:clear
```

4. Abra `/admin/ajuda` e clique em pelo menos: **Primeiros passos**, **Categorias**, **Produtos**, **Cupons**, **Pedidos**, **Gerar etiquetas**, **Criar conta Asaas**, **Criar conta Melhor Envio** e **Assistente de IA**.
5. Teste a busca com: `cupom`, `frete`, `Asaas`, `Groq`, `banner`, `nota fiscal`.

## Como o conteúdo foi protegido contra falhas de instalação

- O pacote incremental desta correção já inclui **os 27 arquivos Markdown completos**, não apenas novos guias.
- Ele também inclui `app/Filament/Support/BundledHelpArticles.php`, com **uma cópia integral do texto dos 27 artigos dentro de PHP**.
- `app/Filament/Pages/Help.php` lê essa versão embutida. Assim, o conteúdo não desaparece mesmo se a pasta `resources/help/` for omitida em uma implantação futura.
- Todos os artigos são entregues escritos de verdade em português, com instruções por etapa, exemplos e dúvidas frequentes.

## Arquivos alterados

- `app/Filament/Pages/Help.php`
- `app/Filament/Support/BundledHelpArticles.php` (novo)
- `resources/help/*.md` (todos os 27 artigos)
- `tests/Feature/Filament/AdminHelpTest.php`

## Compatibilidade

Não possui migrations nem alterações nos módulos comerciais. Não altera `.env` nem `composer.lock`. Não precisa de Node.js, Redis ou `npm run build`. Os artigos ficam incorporados ao código PHP e não dependem de acesso a um banco novo.

Se alguma instrução do manual precisar mudar futuramente, edite o respectivo Markdown e regenere a cópia PHP, pois ela é a versão embutida usada pelo módulo.
