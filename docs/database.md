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

Relationships:

```text
categories 1 --- N products
products   1 --- N product_images
```

Categories have no image.

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

Indexes:

- orders.customer_id
- orders.status
- orders.payment_status
- orders.created_at
- order_status_histories.order_id

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
