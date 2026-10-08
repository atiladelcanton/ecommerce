# Suggested Codex Prompts

## First implementation task — Catalog domain

```text
Read AGENTS.md and all files in docs/ before making changes.

Implement Phase 1 from docs/roadmap.md only: the Catalog domain for categories, products and product images.

Requirements:
- Categories have no image.
- A product belongs to one category.
- Product availability is an enum: immediate, made_to_order, unavailable.
- Money is stored as integer cents.
- Promotional price is optional and valid only when lower than regular price.
- Product supports ordered multiple images and an optional YouTube URL.
- Product includes weight and dimensions for later shipping integration.
- Do not create Filament Resources yet.
- Do not implement cart, customer, orders, payments, shipping or finance.
- Create migrations, Eloquent models, enums as needed, Actions for meaningful mutations, factories, seeders where useful and automated tests.
- Add appropriate indexes from docs/database.md.
- Keep business logic out of controllers/UI.
- Run the test suite and Pint before finishing.

At the end, summarize changed files, tests and any architecture decision you made.
```

## Second task — Catalog Filament

```text
Read AGENTS.md and docs/filament.md. Inspect the existing Catalog implementation first.

Implement only Filament administration for Category and Product.

Do not move business rules into Filament Resources. Reuse Actions/domain rules. Add tests for behavior that can regress. Do not implement Banner yet.

Run tests and Pint.
```

## Third task — Banner

```text
Read AGENTS.md and the Catalog/Banner requirements in docs/.

Implement banners end-to-end for the admin data layer and Filament, but do not redesign the public home page yet.

Fields: title, desktop image, optional mobile image, optional link, alt text, sort order, active, starts_at, ends_at. Add tests for active scheduling logic.
```
