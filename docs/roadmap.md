# Development Roadmap

Work in small reviewed vertical slices. Every phase ends with passing tests and a commit.

## Phase 0 — Foundation (this starter)

- Laravel 13 project skeleton
- Sail with PHP 8.4 + MySQL + Redis
- Filament 5 dependency and installer workflow
- Livewire 4 dependency
- Tailwind CSS 4 + Vite
- PHPUnit
- pragmatic DDD directories
- Codex context documentation

Exit criteria after local setup:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail test
./vendor/bin/sail npm run build
```

## Phase 1 — Catalog domain

Implement only:

- categories
- products
- product images
- availability enum
- pricing rules
- factories/seeders
- automated tests

Do not create cart/orders/payments in this phase.

## Phase 2 — Catalog admin

- Category Filament Resource
- Product Filament Resource
- image management
- validation

Then implement banners + Filament management.

## Phase 3 — Storefront catalog

- branded layout
- header/footer
- hero banners
- categories without category images
- featured/promotional products
- product detail
- WhatsApp quote

## Phase 4 — Search

- search service/action
- indexes
- Livewire autocomplete
- full result page
- performance tests/query review

## Phase 5 — Cart

- guest cart
- quantity/remove
- totals
- merge/ownership strategy
- tests

## Phase 6 — Customer

- account registration/login
- CPF/CNPJ data
- addresses
- customer area
- order history shell

## Phase 7 — Orders and checkout

- order snapshots
- statuses/history
- checkout orchestration
- fake shipping provider
- fake payment gateway

## Phase 8 — Shipping integration

- provider contract finalized
- Melhor Envio adapter first unless client chooses otherwise
- quote selection
- failure handling
- shipment/tracking

## Phase 9 — Payment integration

Choose one first:

- Mercado Pago, or
- Efí

Implement hosted checkout, webhook validation, idempotency and payment history. Add the second gateway only if/when client requires it.

## Phase 10 — Finance

- cash flow entries
- automatic sale inflow after approved payment
- manual inflow/outflow
- simple date-range dashboard

## Phase 11 — Hardening

- SEO metadata and product structured data
- cache review
- image optimization
- authorization review
- rate limiting where needed
- webhook security review
- logs/observability
- backup/deployment checklist

## Incremento aprovado — combinações e frete assistido

A etapa de Shipping inclui normalização da resposta única do Melhor Envio e negociação de frete com conclusão posterior na loja. O catálogo passa a suportar combinações com preço/estoque e medidas próprios; solicitações reservam estoque por 24 horas. O checkout mantém o gateway fake até a fase própria de pagamentos reais. Referência operacional: docs/product-variants.md e docs/shipping.md.
