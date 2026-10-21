# Resultado da validação estrutural dos três CSVs

Validação de formato, chaves lógicas e quantidades em 19/10/2026. Não houve gravação no banco do e-commerce nem download de imagens nesta etapa.

| Categoria | ID no CSV | Produtos |
|---|---:|---:|
| Máquinas de Serigrafia | 2 | 4 |
| Berços | 3 | 13 |
| Tinta Plastisol | 5 | 21 |
| Tinta Base Água | 6 | 13 |
| Acessórios | 9 | 5 |

- 56 slugs distintos, sem duplicação.
- 55 imagens com product_slug existente; um produto sem imagem anunciada.
- 107 registros de opções, todos com slug de produto existente; 93 têm nome/valor, os demais representam dados não recuperados.
- 21 preços numéricos positivos, 35 sem preço.
- 56 registros inativos no CSV.
- As imagens de origem são URLs, ainda não arquivos locais; o Artisan fará o download dentro do projeto.
- As opções não são combinações/SKUs comercializáveis e não devem ser inseridas diretamente em product_variants.
