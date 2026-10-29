# Database Plan

This file describes the intended business schema before modules are implemented. Exact columns may be refined during each vertical slice, but structural changes must update this document.

## Framework tables

Already present in the starter:

- users
- password_reset_tokens
- sessions
- cache
- cache_locks
- jobs
- job_batches
- failed_jobs

`users` is initially the administrative authentication model for Filament. Customer authentication will be modeled separately.

## Planned business tables

### Catalog

- banners
- categories
- products
- product_images
- product_variants

Relationships:

```text
categories 1 --- N products
products   1 --- N product_images
products   1 --- N product_variants
```

Categories have an optional nullable `image_path` (VARCHAR 255), referencing a relative image path on the Laravel `public` disk under `categories/`. Categories without an image keep the typographic Home card. Replacement/removal retains physical files, following the existing Catalog convention.

Implemented Catalog decisions:

- `category_id` is required; deleting a category with products is restricted (no product cascade).
- Deleting a product cascades its image records.
- Slugs and non-null product SKUs are unique. Name changes preserve existing slugs.
- `availability`: `immediate`, `on_demand`, `unavailable`.
- `cost_price`, `price`, `promotional_price`: unsigned BIGINT, integer cents. These names follow the Catalog API; no `_cents` suffix in this module.
- `weight`: decimal(12,3), kilograms; `height`, `width`, `length`: decimal(12,2), centimeters. Eloquent returns decimal strings.
- Optional stock is a nonnegative integer; availability is explicitly managed, not inferred from stock.
- Category indexes: unique slug, sort_order, (is_active, sort_order).
- Product indexes: category_id FK, unique slug, unique nullable sku, availability, price, promotional_price, (is_active, availability), (is_featured, is_active).
- Image gallery index: (product_id, is_primary, sort_order, id), also covers the product FK lookup. No separate redundant product_id index.
- Image actions lock the parent product inside a transaction before any mutation. Use Actions for writes; raw model writes bypass business invariants.
- First added image becomes primary. Removing the primary promotes the next image by gallery order; an empty gallery has no primary.
- Image actions register relative filesystem paths and manage gallery records. Uploads and physical file deletion are outside this slice; removing records retains files, avoiding deletion of shared paths. No storage URL or uploaded file is required by the domain.
- Pricing is derived by `ProductPricing`; discounts are whole percentages rounded down. Promotion must be positive and strictly lower than regular price. Money inputs to Actions must be PHP integers, not floats or numeric strings.
- `cost_price` is hidden from Product array/JSON serialization; future storefront DTOs must also exclude it.
- `CatalogSeeder` runs only in local/testing and can be repeated without duplicating records.

Product variants: `product_variants` stores product_id (FK, cascade on product deletion), name, value, nullable sku, nullable unsigned integer stock, is_active (default true), sort_order (default 0) and timestamps. This slice uses only `name = Cor`; prices remain on Product. The unique (product_id, name, value) constraint prevents repeated colors. The (product_id, is_active, sort_order, id) index supports the active ordered selector. No attribute tables or variant pricing are introduced. Only active colors appear on the product detail; a color must be selected before its WhatsApp quote link is enabled.

`CatalogSeeder` delegates to `DemoCatalogSeeder`: eight categories, thirty products and six colors for each of the two inks. Repeated runs preserve existing product data, variants and images. The previous known demo product fixtures are retired; empty obsolete demo categories are removed. No image fixtures are created.

Banner schema: `banners` stores title, required desktop_image, optional mobile_image/link/alt_text, sort_order (default 0), is_active (default true), nullable starts_at/ends_at and timestamps. Indexes: (sort_order, id) and (is_active, starts_at, ends_at). Images are paths on the public disk. DateTimes use the application timezone, America/Sao_Paulo; visibility includes both boundary instants. Updates validate the combined existing/new interval.

### Customer

- customers
- customer_addresses

Suggested unique/indexes:

- customers.email unique
- normalized customer document unique where business rules allow
- customer_addresses.customer_id

### Cart

- carts
- cart_items

Support guest/session ownership and customer ownership without duplicating line items.

### Order

- orders
- order_items
- order_status_histories
- order_addresses

Indexes:

- orders.customer_id
- orders.status
- orders.payment_status
- orders.created_at
- order_status_histories.order_id

Implemented guest checkout slice:

- `orders`: unique order number and checkout capability, optional customer FK plus immutable customer/address snapshots, operational `status`, independent `payment_status`, integer-cent totals, selected shipping option/provider/service snapshots, carrier/tracking, fiscal shipping document type, NF-e/DC-e keys and logistic timestamps. The selected quote is persisted in `shipping_quote_snapshot` so label generation does not silently recalculate a historical checkout choice.
- `order_items`: order FK with cascade, nullable product/variant FKs with null-on-delete so catalog deletion preserves historical items; product_name/product_sku, nullable variant_name/variant_value, regular_unit_price, effective unit_price, quantity and total snapshots. All monetary columns are unsigned BIGINT integer cents. Indexed order FK.
- `order_addresses`: unique order FK with cascade and postal_code, street, number, nullable complement, neighborhood, city and state snapshots. This is the order's immutable delivery address, independent of future customer address management.
- `order_status_histories`: order FK, optional admin user FK, nullable from_status, to_status, description and timestamps; indexed `(order_id, created_at)`. It records both manual admin transitions and automatic payment/shipping transitions.
- `payments`: order FK, gateway, external ID/status, integer-cent amount, checkout metadata and timestamps. Payment status remains separate from order operational status.
- One transaction writes the order, line/address snapshots, payment approval and status history. Session clearing happens only after commit. Cache locking and the unique checkout_token protect repeated submissions; confirmation access requires the originating session.

### Payment

- payments
- payment_events

External gateway IDs/events used for idempotency must be uniquely constrained.

### Shipping

- `shipping_integrations`: one row per provider, enabled/environment/sort order plus encrypted configuration, credentials and OAuth tokens.
- `shipments`: order FK, provider/external ID, carrier/service snapshots, independent `ShipmentStatus`, customer/provider prices, tracking/label data, quote metadata and logistic timestamps. `(provider, external_id)` is unique when an external ID exists.
- `shipment_events`: shipment FK, unique provider event key, event/status/payload/timestamp for idempotent webhook processing.

`orders` keeps the checkout-level selected shipping snapshot, while `shipments` represents the actual purchased/generated labels. An order may have multiple shipments.

### Commercial reporting

O MVP não mantém um livro-caixa próprio. Faturamento, ticket médio, status e rankings são derivados de `orders`, `order_items` e `payments`. Um módulo financeiro/ERP só deve ser introduzido se houver requisito explícito para despesas, conciliação e caixa real.

### Settings

Implemented table: `store_settings`, with explicit nullable columns for legal_name, cnpj, phone, whatsapp, email, zip_code, street, number, complement, neighborhood, city, state, instagram_url, facebook_url, youtube_url, seo_title, seo_description and shipping_origin_zip_code. `store_name` is required; timestamps track updates.

`StoreSetting::MAIN_ID` is 1. The first save creates the record; opening the admin page is read-only. The generated constant `singleton = 1` has a unique index, enforcing at most one row even for concurrent/raw inserts. It is internal and cannot be assigned another value. The Action uses firstOrCreate plus a transaction and row lock; all saves require store_name and preserve omitted optional fields. There is no settings CRUD or delete UI. No generic key/value table or integration credentials are introduced.

## Money columns

Store monetary values as integer cents. Catalog uses `cost_price`, `price`, and `promotional_price`; the following names remain planned for other contexts:

```text
subtotal_cents
shipping_cents
total_cents
```

Avoid DECIMAL/float drift in application math.

## Shipping configuration and resumable operations

Incremental shipping migrations add `shipping_carriers`, `shipping_services`, `shipping_agencies` (integration/environment/external identity, local activation and API availability), agency defaults per carrier, integration references on orders/shipments, package snapshots and unique label operation identities. `shipment_events` gains processing timestamps. Each planned fulfillment slot has numbered attempts; cancelling a label does not remove the slot. Store sender IE/CNAE belong to `store_settings`. Existing migrations and monetary/history columns are preserved.

Legacy shipping upgrade adds `orders.shipping_environment` and links existing shipping records to their current integration/environment, without rewriting names, prices or quote snapshots. New orders persist the environment explicitly.

Agency/carrier membership is many-to-many through `shipping_agency_carrier` (composite primary key on agency/carrier IDs, cascading only pivot membership on deletion). The incremental migration backfills legacy associations, existing carrier defaults and API `metadata.companies` within the same integration/environment. Agency primary keys, shipment references and carrier defaults are preserved. Legacy `shipping_carrier_id` / `company_external_id` remain for rollback compatibility; new selection and shipment validation use pivot membership.

## Product combinations, assisted shipping and inventory reservations

Incremental migrations extend `product_variants` with JSON `attributes`, a canonical `combination_key` unique per product, nullable integer-cent `price` / `promotional_price`, and nullable package weight/dimensions. Legacy color IDs and prices remain unchanged; null prices inherit the parent. `order_items` gains immutable attribute and measurement snapshots.

`orders.shipping_pricing_status` distinguishes awaiting_quote, quoted and confirmed; legacy/automatic orders remain null. Shipping/total cents are nullable while freight is unknown. `order_shipping_quotes` stores immutable numbered revisions, admin author, carrier/service/instructions/estimate, integer-cent price, expiry, acceptance and supersession. Order items/prices and addresses are frozen before manual freight is negotiated.

`inventory_reservations` has one unique row per order item, the product/variant stock pool, quantity and lifecycle timestamps (expires, consumed, released, restored). Stock stays physical on the catalog; available stock subtracts unexpired reservations. Only newly created orders participate. Reservation creation, payment consumption and commercial cancellation lock the order and stock pools in a consistent order. Made-to-order products do not consume immediate physical stock.


## Cupons de desconto (27/10/2026)

`coupons`: código único, descrição, `discount_type`, `value` (percentual inteiro ou valor fixo em centavos), `scope`, `minimum_subtotal_cents`, `maximum_discount_cents`, `usage_limit`, `allow_sale_items`, vigência e ativação. Os pivôs `coupon_categories` e `coupon_products` delimitam o alcance. `coupon_usages` acompanha estado reservado/consumido/liberado por pedido; `coupon_claims` aplica unicidade de CPF/CNPJ e limite enquanto reservado ou consumido. `orders.coupon_id`, `orders.coupon_code` e `orders.discount_cents` armazenam o desconto contratado. Ver `docs/coupons.md`.


## Blog opcional (2026-10-28)

A migration incremental `2026_10_28_000001_create_blog_posts_and_settings.php` adiciona duas colunas booleanas a `store_settings`, com default `false`: `blog_enabled` (rotas e menu públicos) e `blog_show_on_home` (seção de artigos na Home, dependente da primeira flag). O registro singleton e todos os dados anteriores permanecem intactos.

A tabela `blog_posts` possui `id`, `author_id` opcional (FK para `users`, `nullOnDelete`), `title`, `slug` único, `excerpt`, `content` sanitizado, `cover_image`, `cover_alt`, `category` opcional, `tags` JSON, `seo_title`, `seo_description`, `status` (`draft` ou `published`), `published_at`, timestamps e índice `(status, published_at, id)`. Imagens usam caminhos relativos no disco `public`; apagar registro preserva o arquivo físico. Para mais detalhes, veja `docs/blog.md`.

## Identidade visual por instalação (2026-10-29)

Migration incremental adiciona `store_settings.logo_path` (VARCHAR nullable, logo no disco `public` em `store/branding/`) e `color_primary`, `color_secondary`, `color_button` (CHAR(7) nullable). HEX validado na Action; coluna `null` significa a cor padrão do template ativo. Nenhuma tabela, restrição de negócio ou selecionador de template é adicionada ao banco.
