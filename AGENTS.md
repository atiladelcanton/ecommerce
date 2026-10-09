# SABA Máquinas — Agent Instructions

This file is the primary instruction set for Codex and other coding agents working in this repository.

## 1. Product

SABA Máquinas is a focused e-commerce for screen-printing machines and related products. Keep the user experience simple and commercial. Do not turn the application into an ERP, marketplace, CRM, multi-store platform, or generic commerce framework.

## 2. Stack

- Laravel 13
- PHP 8.4 in Sail
- MySQL 8.4
- Redis
- Filament 5 for the administrative back office
- Livewire 4 for reactive storefront behavior
- Tailwind CSS 4
- PHPUnit for automated tests
- Laravel Sail for local development

Do not introduce React, Vue, Inertia, Elasticsearch, OpenSearch, Kafka, or a microservice unless an explicit approved requirement justifies it.

## 3. Architecture

Use pragmatic DDD, not ceremonial DDD.

Top-level business contexts:

- Catalog
- Customer
- Cart
- Order
- Payment
- Shipping
- Finance
- Settings

Business logic must not live in:

- Controllers
- Route closures
- Filament Resources / Pages
- Livewire Components
- Blade views

Use Actions for application use cases. Use domain objects/enums/value objects only when they remove ambiguity or enforce a real invariant. Use Contracts for infrastructure that can vary, especially payment and shipping providers.

Expected structure:

```text
app/
  Actions/
    Catalog/
    Customer/
    Cart/
    Order/
    Payment/
    Shipping/
    Finance/
  Domain/
    Catalog/
    Customer/
    Cart/
    Order/
    Payment/
    Shipping/
    Finance/
    Settings/
  Infrastructure/
    Payments/
    Shipping/
  Livewire/
  Filament/
```

Do not create repositories around every Eloquent model. Eloquent may be used directly inside Actions where there is no meaningful abstraction benefit.

## 4. Coding rules

- Add `declare(strict_types=1);` to new application PHP classes.
- Prefer `final` classes unless extension is intentional.
- Use typed parameters, typed properties and return types.
- Prefer PHP Enums over magic status strings.
- Prefer dedicated DTOs for external API payloads.
- Use database transactions for operations that modify multiple financially relevant records.
- Money must never use float. Persist money as integer cents unless a documented integration forces another representation.
- Historical order item values must be snapshotted at purchase time; never recompute old orders from the current Product price.
- Webhooks must be idempotent.
- External IDs used to deduplicate webhook events must be uniquely constrained.
- Never log payment secrets, access tokens, passwords, full card data, or other credentials.
- Do not expose cost price on the storefront or public API.

## 5. Filament rules

Filament is the admin UI, not the domain layer.

Resources may:

- define forms and tables;
- authorize actions;
- call application Actions;
- display domain state.

Resources must not contain complex pricing, order, payment, shipment, or financial rules.

## 6. Livewire rules

Use Livewire for:

- search autocomplete;
- cart interactions;
- address selection;
- shipping quote interaction;
- checkout UI where it improves UX.

Keep components thin. Components call Actions.

Search autocomplete must debounce requests and cap suggestion counts. Do not query unbounded result sets.

## 7. Storefront requirements

- Visitors can add products to cart without authentication.
- Authentication is required only when necessary to finalize/account for the order.
- Product detail supports photo carousel and optional YouTube demonstration video.
- Product availability: immediate, made to order, unavailable.
- Promotional price appears only when valid and lower than regular price.
- Products can expose a WhatsApp quote button using a store-configured phone number and standardized message.
- Categories have an optional featured image; Home uses a typographic fallback when absent.
- Home contains rotating hero banners, category navigation, product listings and an automatically shown Promotions section when valid promotional products exist.

## 8. Payments

Use a `PaymentGateway` contract.

Initial planned adapters:

- Mercado Pago
- Efí

The application must trust signed/validated gateway webhooks for payment state, not the browser redirect alone.

Build the order/payment flow first against a fake implementation so application behavior can be tested without real gateway credentials.

## 9. Shipping

Use a `ShippingProvider` contract.

Initial planned adapters:

- Melhor Envio
- Correios, if the chosen integration remains viable for the client's operation

Product weight and dimensions must be available for shipping quotation.

Build against a fake provider before real external integration.

## 10. Testing

Every meaningful business rule requires automated tests.

At minimum test:

- valid/invalid promotional pricing;
- cart totals and quantity changes;
- order snapshot pricing;
- order state transitions;
- payment webhook idempotency;
- duplicate external payment events;
- shipping quote failures/fallback behavior;
- finance entry generation from approved payments.

Before considering a task finished:

```bash
./vendor/bin/sail test
./vendor/bin/sail pint --test
```

If the task changes frontend assets, also run:

```bash
./vendor/bin/sail npm run build
```

## 11. Database and migrations

Read `docs/database.md` before creating tables.

Do not create business tables outside the documented naming plan without updating that document in the same change.

Create appropriate indexes for storefront and admin access patterns.

## 12. Scope discipline

Before adding a dependency or abstraction, ask: does a current approved requirement need it?

Do not proactively add:

- wishlist;
- reviews;
- loyalty points;
- affiliates;
- product comparison;
- coupons;
- multi-warehouse;
- marketplace sellers;
- fiscal/NF-e engine;
- full accounting/ERP features.

## 13. Work sequence

Follow `docs/roadmap.md`.

Implement one vertical slice at a time. Do not generate the whole e-commerce in one task.

A normal change sequence is:

1. read AGENTS.md and relevant docs;
2. inspect existing implementation;
3. implement only the requested slice;
4. add/update tests;
5. run tests and formatter;
6. summarize changed files and architectural decisions.


## Admin UI

The Filament administrative interface follows the SABA design system.

### Layout
- Administrative pages should use the maximum practical content width.
- Index tables must use the full available content area.
- Avoid narrow centered containers on desktop.
- Prefer responsive grids for forms.

### Branding
- SABA red is the primary accent.
- White cards on very light gray backgrounds.
- Graphite/black text.
- Red must not dominate large surfaces.

### Tables
- Full width.
- Subtle borders.
- Compact, readable rows.
- Light table headers.
- Filters/search aligned efficiently.

### Visual consistency
New Filament Resources must follow the same layout and visual conventions.
Do not introduce independent visual styles per module.

### Forms / fieldsets — mandatory permanent convention

- Every administrative form must group its fields semantically inside Filament Sections or Fieldsets styled as cards. Even small forms need at least one card; never place fields directly on the page background.
- Use contained components, with white background, a subtle 1px light-gray border, 8–12px radius, comfortable internal padding and consistent vertical spacing. Avoid heavy shadows; inherit the shared admin theme instead of styling each Resource independently.
- Category uses one Section titled “Informações da categoria”, containing name, slug, description, sort order and active status.
- Product uses six Sections: Informações, Mídia, Comercial, Disponibilidade, Transporte and Publicação.
- Inside cards, use responsive grids: one column on mobile and two, three or four on desktop when appropriate. Long descriptions, editors and image uploads span the full row.
- Final form actions may stay outside the cards, immediately below the last card.
- Apply this convention to all existing and future administrative Resources and action forms, including Categories, Products, Banners, Customers, Orders, Settings, Finance and Users. Do not introduce ungrouped fields on the page background.

### Monetary fields — mandatory shared convention

- All admin money inputs must use `App\Filament\Forms\Components\BrazilianMoneyInput`, initially Product cost_price, price and promotional_price. Show an `R$` prefix, dot thousands separator and comma decimal separator with two places.
- Digit entry is in cents: `1` → `R$ 0,01`, `100` → `R$ 1,00`, `129990` → `R$ 1.299,90`. Empty optional values remain null. Use numeric mobile keyboards and reject invalid input.
- The shared field formats integer cents on hydration and parses the mask on dehydration through `BrazilianMoney`. Do not repeat conversion in Resources, Pages or per-module helpers. Actions receive integer cents, never formatted strings or floats.
- Use `BrazilianMoney::display()` for administrative monetary listings. Preserve precision with strings and integer cents; do not use float, parseFloat or floating-point formatting for money.
- Product cost must be >= 0; selling price is required and > 0; optional promotional price must be > 0 and strictly lower than selling price. These rules belong in the existing Catalog validation/domain layer and must return friendly Portuguese field errors.
- Reuse this same input and formatter for future order, cash-flow, shipping, discount and payment amounts; do not implement different monetary masks per module.
