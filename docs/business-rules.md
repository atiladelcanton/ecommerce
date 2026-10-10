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

The public cart is stored in the Laravel session under `store.cart.items`. Each line stores only `product_id`, nullable `product_variant_id` and `quantity`. Identical product/variant pairs accumulate quantity; distinct colors stay on separate lines. Quantity must be an integer from 1 to 999. Decreasing at 1 is disabled; removing an item is a separate explicit action. Clearing the cart requires a UI confirmation.

Products with any registered Cor variant require a selected active color belonging to that product, even if all colors are currently inactive. Inactive or unavailable products cannot be added; made-to-order products can. Stock reservation and stock limits are outside this slice.

Names, images, colors and current effective prices come from the catalog. Subtotals use integer cents and current promotional pricing. The badge counts total units, rather than distinct lines. Deleted, inactive, unavailable or invalid-variant lines are removed when reading the cart. The cart does not snapshot or persist an order. Shipping is displayed as “Calculado no checkout”; the checkout button links to `/checkout`.

Persist or merge the guest cart into the customer flow when identification is implemented in a future slice.

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
- confirmed (implemented initial demo checkout)
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

The implemented `/checkout` does not require an account. Customer name/email/phone/CPF-or-CNPJ and a complete delivery address (complement optional) are stored as order snapshots. Phone, document and CEP formatting is stripped; document accepts 11/14 digits without checksum or external identity verification in this slice. UF must be a Brazilian state code. These guest details do not create a customer account.

Backend catalog prices and variants are reloaded and locked at completion. The shipping provider returns “Entrega padrão” for 4990 cents with a demonstrative 5–8 business-day range, or “Retirada / combinar com a loja” for zero. The selected option ID is validated against the backend quote. Subtotal is the sum of snapshotted line totals, discount is zero, and total is subtotal plus shipping minus discount, all integer cents. Browser-provided prices, totals, names and discount are ignored.

“Finalizar pedido de teste” creates the pending order, immutable product/color/address snapshots, initial history, approved fake payment and confirmed status/history in one transaction. Session cart/token are cleared after success; failure rolls back the records and retains the cart. A unique checkout UUID plus cache lock prevents duplicate orders/payments on double submit. Replaying an old successful token returns its order without clearing a newer cart. Numbers follow `SABA-000001` and are unique.

`/pedido/{number}/confirmacao` shows only stored snapshots and requires the originating session, returning 404 to another visitor. Payment reads “Aprovado — ambiente de demonstração”. The additional “Este pedido utilizou pagamento simulado.” notice appears only in local/development. Real gateways, shipping integration, order admin, accounts, stock reservation and finance entries are not part of this slice.

Future real-integration flow:

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
