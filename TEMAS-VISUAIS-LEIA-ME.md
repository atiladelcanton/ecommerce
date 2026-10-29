# Vitrine: 5 temas visuais — instalação

## Atualização

Extraia o ZIP incremental **na raiz do projeto Laravel** preservando as pastas e substituindo os arquivos existentes. Antes da instalação, faça backup do banco e dos arquivos alterados.

```bash
php artisan migrate --force
php artisan optimize:clear
```

Acesse **Admin → Configurações → Temas da loja** (`/admin/store-templates`), escolha um cartão e clique **Aplicar tema**. A seleção é imediata para a loja pública e exige usuário do grupo **Administrador**. Usuários Operacionais não podem acessar a tela nem enviar alterações diretamente.

**Não precisa de `npm run build`**: os estilos específicos dos novos temas estão em `public/css/storefront/templates/creative.css`. A compilação Vite preexistente (`public/build`) não é substituída.

## Opções disponíveis

| ID | Nome | Inspiração |
| --- | --- | --- |
| 1 | Tema padrão | Visual original preservado |
| 2 | Mercatto | Layout comercial já existente (antigo Warehouse Inspired) |
| 3 | Horizonte | Navegação e campanhas amplas inspiradas no OceanWP Store |
| 4 | Essência | Minimalismo editorial inspirado no Botiga |
| 5 | Pulso | Visual urbano para moda/camisetas; referência Shopify fornecida não estava acessível para inspeção direta |

O código dos temas foi construído com Blade/CSS próprios. Não inclui arquivos, imagens ou scripts proprietários dos sites de referência.

## Troca por terminal

O comando Artisan continua disponível e agora persiste a mesma opção utilizada na tela administrativa (tabela `store_settings`, coluna `template_id`):

```bash
php artisan switch:template --list
php artisan switch:template --current
php artisan switch:template --template=4
```

A seleção anterior em `.env` (`STOREFRONT_TEMPLATE`) permanece como fallback **até que seja escolhida uma opção na tela ou no comando**. Após a primeira escolha, prevalece o banco. Isso evita uma diferença de tema entre terminal e painel.

## Comportamento e restrições

- Todos os temas utilizam os produtos, categorias, banners, promoções, preços, estoque, URLs, carrinho, checkout, cupons, Asaas, Melhor Envio e blog que já existem.
- O usuário pode continuar alterando logo e cores na tela de Configurações da loja.
- Banners dependem das imagens cadastradas. Se não existir banner ativo, cada tema tem um cabeçalho de campanha alternativo.
- O tema 5 foi desenvolvido como interpretação de linguagem de moda urbana, **não reprodução fiel da demonstração Shopify**.
- A migration é apenas aditiva (`store_settings.template_id`), sem mexer em pedidos e pagamentos.

## Testes recomendados

1. Abrir cada tema na home e na listagem de produtos, incluindo no celular.
2. Abrir um produto com variações e adicionar ao carrinho.
3. Testar descontos, frete, checkout e acesso à conta.
4. Confirmar blog e banners quando habilitados.
5. Entrar como Operacional e verificar o acesso negado ao seletor.
6. Conferir se a troca mantém as cores da marca e o logotipo.

O ambiente que gerou este pacote não contém a pasta `vendor`: execute os testes automatizados no ambiente local após instalar dependências.
