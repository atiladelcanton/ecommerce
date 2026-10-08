# Business Rules

## Categories

Fields:

- name
- slug
- description (optional)
- sort order
- active

Categories do **not** have an image.

A product initially belongs to one category.

## Products

Planned fields:

- name
- slug
- sku/code
- category
- short description
- full description
- product images
- primary image marker/order
- optional YouTube URL
- availability enum: immediate / on_demand / unavailable
- cost price
- selling price
- promotional price (nullable)
- optional stock quantity
- weight
- height
- width
- length
- active
- featured
- allow WhatsApp quote

Cost price is administrative only.

Promotional price is effective only when it is non-null, positive and strictly lower than regular selling price.

## Banners

Planned fields:

- title
- desktop image
- optional mobile image
- optional link
- alt text
- sort order
- active
- optional starts_at
- optional ends_at

Banners require a title and desktop image. Only active banners whose optional start/end interval contains the current time are currently visible (inclusive endpoints). Both open-ended schedules and equal start/end instants are allowed. End before start is rejected, including partial updates. Links, when present, are absolute HTTP/HTTPS URLs. Gallery files remain on disk when a banner or an image reference is removed.

## Store settings

A single main configuration is created on the first successful save. Store name is required; other identity, contact, address, social, SEO and origin-CEP fields are optional. Optional values can be cleared to null. Whitespace is trimmed, UF is uppercased, and phone/WhatsApp values retain their entered formatting and country code. CEP accepts 8 digits with an optional hyphen; CNPJ is stored as entered, without checksum validation in this slice. No external lookup or integration is performed.

## Customer

Required account data:

- name
- email
- CPF or CNPJ
- phone
- password

Model document type explicitly (PF/PJ) when the Customer module is implemented.

For PJ, prepare for:

- company/legal name
- trade name
- CNPJ
- optional state registration

## Addresses

Customers can have multiple addresses.

Planned fields:

- label
- postal code
- street
- number
- complement
- neighborhood
- city
- state

## Cart

Guest users can create and modify a cart before registration/login.

Persist or merge the guest cart into the customer flow when identification occurs.

## Orders

Order item snapshots must store at least:

- product id reference
- product name
- SKU/code
- quantity
- regular unit price at purchase time
- effective unit price at purchase time
- subtotal

Order status and payment status are separate concepts.

Planned order status:

- pending
- processing
- shipped
- delivered
- cancelled

Planned payment status:

- pending
- approved
- failed
- cancelled
- refunded

## WhatsApp quote

A product may have a “Cotar pelo WhatsApp” action.

The store phone number is configuration, not hardcoded.

Default message concept:

> Olá! Tenho interesse no produto {product_name}. Gostaria de mais informações e de uma cotação. {product_url}

URL-encode the final message.

## Search

Autocomplete should search prioritized product identifiers such as name and SKU, and may include category. Keep suggestion results bounded.

Start with database-backed indexed search appropriate to catalog size. Keep the search service boundary replaceable if the catalog later justifies a dedicated engine.

## Checkout

Flow:

1. cart
2. customer identification/account
3. address
4. shipping quote/selection
5. order review
6. order creation
7. gateway checkout creation
8. redirect to gateway hosted checkout where applicable
9. webhook validates payment result
10. order/payment state updates

Browser return URLs are informational; the webhook is authoritative for paid status.

## Finance

Keep it intentionally small.

Entry types:

- inflow
- outflow

Sources/categories may include sales, purchase, freight, expense and other.

A payment approval can create one sale inflow. Idempotency must prevent duplicates.
