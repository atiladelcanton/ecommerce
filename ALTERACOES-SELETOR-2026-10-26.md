# Seletor público de variações — 26/10/2026

**Escopo:** somente página pública de produto, a Action que prepara os dados dessa página, testes e documentação. Sem migrations nem dependências novas.

## Como ficou

- Produtos com **1 ou 2 características**: botões de seleção imediata (com amostra para cor). A segunda característica é exibida após a primeira escolha. Opções sem estoque são visíveis, porém desativadas.
- Produtos com **3 ou mais características**: combinação completa em um único botão, com nome, características, preço e estado de disponibilidade. Acima de 8 opções aparece **Ver todas as opções**.
- Seleção atualiza preço, oferta/variante para o carrinho e link de cotação por WhatsApp usando a mesma regra existente do domínio, sem reload da página.
- Layout responsivo com foco de teclado, estados selecionado/desabilitado e toques de no mínimo 44 px.

## Aplicação pelo ZIP de alterações

Extraia a pasta ZIP na raiz do repositório SABA e permita a substituição dos arquivos. Os caminhos existentes são preservados. Não execute `migrate:fresh` nem restaure banco de dados.

## Após aplicar

```bash
./vendor/bin/sail npm run build
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail test --filter=ProductCombinationsTest
./vendor/bin/sail test --filter=ProductVariantsTest
./vendor/bin/sail pint --test
```

## Validação nesta geração

- `php -l` dos quatro arquivos PHP alterados: OK.
- Testes JavaScript nativos: 17 aprovados, 0 falhas.
- PHPUnit/Laravel: **não executados**, pois o ZIP fornecido não contém `vendor/`.
- Build Vite: **não executado com sucesso**, pois não existe `node_modules/` no ambiente da entrega (`vite: not found`).

Os testes de feature adicionados precisam ser executados no projeto com as dependências instaladas, antes de publicar em produção.

## Segurança do pacote completo

O ZIP de projeto completo é uma cópia **sem** `.env`, `.git`, banco SQLite e arquivos privados/logs/caches de runtime, para evitar compartilhar credenciais e dados pessoais. Para preservar as configurações e o banco local existentes, prefira aplicar o ZIP de alterações ao seu próprio repositório.
