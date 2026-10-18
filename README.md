# Saba Equipamentos — E-commerce Starter

Starter técnico para o novo e-commerce da Saba Equipamentos.

## Stack definida

- Laravel 13
- PHP 8.4
- Laravel Sail
- MySQL 8.4
- Redis
- Filament 5
- Livewire 4
- Tailwind CSS 4
- PHPUnit

A arquitetura e as regras de trabalho para Codex estão em `AGENTS.md` e `docs/`.

## Importante sobre este ZIP

O diretório `vendor/` e `node_modules/` não são versionados nem incluídos. Isso é intencional e é o padrão para projetos Laravel. Execute o setup uma vez na sua máquina para resolver as dependências nas versões compatíveis do `composer.lock` que será gerado localmente.

O script funciona com Composer local ou, se você não tiver Composer, usa a imagem oficial do Composer via Docker.

## Instalação rápida

Linux / macOS / WSL2:

```bash
chmod +x scripts/setup.sh
./scripts/setup.sh
```

Depois:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail test
./vendor/bin/sail artisan make:filament-user
```

Acesse:

- Storefront: `http://localhost:8081`
- Filament: `http://localhost:8081/admin`

## Alternativa manual

```bash
cp .env.example .env
composer install
php artisan key:generate
npm install
npm run build
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail test
```

Se estiver trabalhando exclusivamente dentro do Sail, use `./vendor/bin/sail composer`, `./vendor/bin/sail npm` etc. depois que as dependências iniciais estiverem presentes.


## Portas do ambiente local

Para evitar conflito com outros projetos Docker/Sail, este starter não usa as portas mais comuns no host:

- Aplicação: `8081` → container `80`
- Vite: `5174`
- MySQL: `3307` → container `3306`
- Redis: `6380` → container `6379`

As portas internas dos containers continuam as padrões; somente o encaminhamento para a máquina host foi alterado.

## Identidade visual do administrativo

O painel Filament já está configurado com o logo da **SABA Equipamentos Serigráficos** na tela de login. O arquivo está em `public/images/saba-logo.jpeg` e a configuração fica em `app/Providers/Filament/AdminPanelProvider.php`.

## Estrutura

```text
app/
├── Actions/
├── Domain/
│   ├── Catalog/
│   ├── Customer/
│   ├── Cart/
│   ├── Order/
│   ├── Payment/
│   ├── Shipping/
│   ├── Finance/
│   └── Settings/
├── Infrastructure/
│   ├── Payments/
│   └── Shipping/
├── Filament/
└── Livewire/

docs/
├── architecture.md
├── business-rules.md
├── database.md
├── frontend.md
├── filament.md
├── roadmap.md
└── codex-prompts.md
```

## Próximo passo recomendado

Não peça ao Codex para criar todo o e-commerce.

Abra `docs/codex-prompts.md` e execute o primeiro prompt: **Catalog domain**.

Quando essa entrega estiver testada e revisada, faça commit e só então avance ao Filament do catálogo.

## Comandos úteis

```bash
make up
make down
make test
make migrate
make fresh
make build
make pint
```

## Convenções essenciais

- categorias não têm imagem;
- preço de custo nunca aparece no storefront;
- dinheiro é armazenado em centavos inteiros;
- preço histórico de pedido é snapshot, não leitura do produto atual;
- webhooks de pagamento devem ser idempotentes;
- Filament/Livewire não carregam regras de negócio complexas;
- integrações de pagamento e frete entram atrás de contratos.

## Portas locais reservadas

Este projeto usa portas diferentes das padrões para coexistir com outros projetos locais:

- HTTP: `8081`
- MySQL no host: `3307`
- Redis no host: `6380`
- Vite: `5174`

Dentro da rede Docker, Laravel continua usando `DB_HOST=mysql`, `DB_PORT=3306` e `REDIS_PORT=6379`.

Se você reutilizou uma pasta de uma versão anterior, rode novamente `./scripts/setup.sh`: o script corrige as portas no `.env`, limpa caches e publica os assets do Filament.
