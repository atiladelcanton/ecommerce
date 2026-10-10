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

- `orders`: unique nullable `number` (assigned as `SABA-` plus the database ID padded to at least six digits within the creation transaction), unique UUID `checkout_token`, indexed SHA-256 `checkout_session_hash`, customer name/email/phone/document snapshots, `status` (`pending`/`confirmed`), independent `payment_status`, unsigned BIGINT `subtotal_cents`, `shipping_cents`, `discount_cents` (initially zero), `total_cents`, shipping option ID/name/description snapshots and timestamps. Indexes on status, payment_status and created_at. No customer account is required or created.
- `order_items`: order FK with cascade, nullable product/variant FKs with null-on-delete so catalog deletion preserves historical items; product_name/product_sku, nullable variant_name/variant_value, regular_unit_price, effective unit_price, quantity and total snapshots. All monetary columns are unsigned BIGINT integer cents. Indexed order FK.
- `order_addresses`: unique order FK with cascade and postal_code, street, number, nullable complement, neighborhood, city and state snapshots. This is the order's immutable delivery address, independent of future customer address management.
- `order_status_histories`: order FK, nullable from_status, to_status, description and timestamps; indexed `(order_id, created_at)`. Creation records pending and confirmation records confirmed.
- `payments`: order FK, gateway, globally unique external_id, status, unsigned BIGINT amount_cents, is_demo and timestamps. No webhook/event table or real payment integration is introduced in this slice.
- One transaction writes the order, line/address snapshots, payment approval and status history. Session clearing happens only after commit. Cache locking and the unique checkout_token protect repeated submissions; confirmation access requires the originating session.

### Payment

- payments
- payment_events

External gateway IDs/events used for idempotency must be uniquely constrained.

### Shipping

- shipments

### Finance

- cash_flow_entries

Link to order/payment where relevant but keep manual entries possible.

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
