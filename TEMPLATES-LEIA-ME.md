# SABA — Templates visuais por instalação

## Objetivo e limites

Esta atualização não altera tabelas, Models, Actions, controllers de negócio, rotas, componentes Livewire ou painel Filament. A escolha do visual é uma configuração do **ambiente de implantação**, não uma configuração da loja. Os temas compartilham o mesmo catálogo, banners, checkout, pagamentos, cupons, blog, frete e dados.

- `1` — SABA Original. É o padrão e continua com os mesmos componentes Blade e estilos que já existiam.
- `2` — Warehouse Inspired. Visual novo inspirado na organização do demo 14 da IQIT, feito do zero para a loja SABA (sem copiar arquivos proprietários da IQIT).

## Instalação

1. Extraia o ZIP incremental e copie a estrutura de pastas para a raiz do projeto Laravel. **Não** apague pastas antigas.
2. O pacote não inclui `.env`, `composer.json`, `composer.lock`, `vendor`, arquivos de armazenamento, nem o build existente.
3. Execute na raiz da aplicação:

```bash
php artisan config:clear
php artisan switch:template --list
php artisan switch:template --template=2
php artisan switch:template --current
```

A mudança será persistida no `.env` da instalação como `STOREFRONT_TEMPLATE=2`. O comando limpa somente o cache de configuração, sem depender de Redis. Não executa migrations. Nenhum `npm run build` é necessário: o CSS novo já está em `public/css/storefront/templates/warehouse.css`. Os arquivos Vite existentes continuam intactos.

Voltar à identidade original:

```bash
php artisan switch:template --template=1
```

## Para vários clientes

Distribua o mesmo código a cada instalação e execute o comando correspondente em cada servidor, usando o `.env` próprio. Nenhum administrador da loja pode trocar o template via Filament. O template modifica **apenas a renderização da loja pública**.

## O que muda no template 2

- Faixa superior e header próprios.
- Menu de departamentos, com lista dinâmica das categorias cadastradas.
- Home com departamento lateral, banner já cadastrado, atalhos, categorias, vitrines e blog quando habilitado.
- Cards de produtos próprios; os preços, ofertas e disponibilidades continuam vindo dos componentes existentes.
- CSS diferenciado para páginas de produto, listagens, checkout e demais páginas públicas, mantendo os Livewire originais.
- Rodapé distinto.

## Limites e verificação

- Não inclui novas opções de gerenciamento de layout no Filament.
- Não replica widgets ou módulos proprietários de PrestaShop; algumas funcionalidades da referência não existem no SABA e não foram simuladas (lista de desejos, marcas, etc.).
- Os testes PHP precisam ser executados no projeto com `vendor` instalado.
- Teste especialmente: navegação mobile, autocomplete, produtos com variação, cupons, checkout, frete, blog e área do cliente em ambas as identidades.
- Como o pacote foi construído sobre a versão `saba-blog-taxonomias-projeto-completo.zip`, confira alterações locais feitas depois desse pacote antes de sobrescrever os três arquivos Blade existentes.

## Próximos templates

Para adicionar um tema 3: registrar o ID em `config/storefront_templates.php`, fornecer seus componentes em diretórios próprios, adicionar seus estilos estáticos ou build próprio e selecionar a variante na camada Blade. **Não** duplicar classes de negócio. 
