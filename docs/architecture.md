# Architecture

## Goal

Keep Saba Equipamentos easy to understand, test and evolve while avoiding unnecessary enterprise ceremony.

## Layers

### Domain

Business concepts, invariants, enums and focused value objects.

### Actions

Application use cases such as `CreateOrderAction`, `ApprovePaymentAction`, `ShipOrderAction` and `RegisterCustomerAction`.

### Infrastructure

Adapters for external systems such as Mercado Pago, Efí and Melhor Envio.

### Delivery/UI

- Filament for administration.
- Livewire + Blade + Tailwind for storefront interaction.

UI layers orchestrate and validate input, then delegate to Actions.

## Contexts

### Catalog
Categories, products, images and banners.

### Customer
Customer identity, document data, credentials and addresses.

### Address lookup

Checkout address assistance uses `Domain/Address/Contracts/PostalCodeLookup` and the immutable `AddressData` DTO. `Actions/Address/LookupPostalCodeAction` normalizes and validates eight-digit CEPs before any request and caches found/not-found results for 24 hours, separately by configured provider. Failures are not cached. `Infrastructure/Address/ViaCepPostalCodeLookup` uses Laravel HTTP Client over HTTPS with a 4-second timeout and 3-second connection timeout; it maps provider payloads and converts HTTP/connection/malformed-response failures into a handled `PostalCodeLookupFailed` exception.

`config/address.php` defaults to `viacep`. The container binding is the provider selection point; a future BrasilAPI adapter can implement the same contract and be registered there. BrasilAPI is not implemented. The address lookup remains independent of shipping quotation and order/payment persistence. Lookup failures leave manual address entry available.

### Cart
Guest/authenticated shopping cart and cart items.

The public cart uses `Domain/Cart/CartItem` for line identity, quantity invariants and integer subtotal arithmetic, `Actions/Cart` for catalog validation and mutations, and `Infrastructure/Cart/SessionCart` for session serialization. Storefront Livewire components call Actions and emit a local `cart-updated` UI event to refresh the badge. Session lines contain IDs and quantity only; public catalog queries provide presentation and effective prices. Customer ownership/merge remains a future slice.

### Order
Order creation, immutable line snapshots, status history and fulfillment state.

The guest checkout is orchestrated by `Actions/Order/CreateInitialOrderAction`. It locks catalog rows, reuses Cart pricing/variant validation, validates a backend shipping option and writes order/item/address snapshots, two status-history entries and an approved demo payment in one database transaction. A cache lock and unique checkout token provide idempotency. Only after commit does the originating checkout clear its session cart/token. `GetOrderConfirmationAction` reads snapshots only and restricts access to the originating session hash; no login or customer account is involved.

### Payment
Gateway abstraction, payment attempts, external events and webhook processing.

The implemented slice binds `Domain/Payment/Contracts/PaymentGateway` to `Infrastructure/Payments/FakePaymentGateway`. Typed PaymentRequest/PaymentResult DTOs keep the adapter separate from Livewire and order persistence. The fake adapter returns an approved demo result with a deterministic external ID based on the checkout token. Real gateways/webhooks and finance side effects are future slices.

### Shipping
Shipping is provider-agnostic and supports multiple enabled delivery integrations simultaneously. `ShippingProviderRegistry` resolves enabled providers from `shipping_integrations`; `QuoteShippingAction` merges normalized quotes and the selected quote is snapshotted into the order.

Capabilities are split between `ShippingProvider` (quotes), `ShipmentProvider` (cart/purchase/inspection), `ShipmentLabelProvider` (labels/cancellation), and `ShipmentTrackingProvider` (tracking). The initial real adapter is Melhor Envio with OAuth, quote, purchase/generate/print, cancellation, tracking and signed/idempotent webhooks. Local pickup is an independent provider. `Order hasMany Shipments`, so the order workflow is not coupled to the assumption of one label. See `docs/shipping.md`.

### Finance
Small cash-flow ledger only. It is not an accounting package.

### Settings
Store identity, WhatsApp, origin postal code and provider credentials/configuration references.

## Integrations

Use a Ports & Adapters boundary for payment and shipping integrations.

```text
Order/Checkout
   -> PaymentGateway contract
      -> FakePaymentGateway
      -> MercadoPagoGateway
      -> EfiGateway

Checkout
   -> QuoteShippingAction
      -> ShippingProviderRegistry (active integrations)
         -> MelhorEnvioShippingProvider
         -> LocalPickupProvider
```

## Money

Use integer cents internally and in the database whenever possible.

Example: R$ 12.999,90 = `1299990` cents.

Create a Money value object only when enough rules exist to justify it; do not add it merely for aesthetics.

## Transactions

Use database transactions for order creation, payment approval side effects, refunds/cancellations that affect finance, and other multi-record critical operations.

## Events

Domain/application events are welcome when they decouple real side effects. Do not create events for simple local method calls.

## Customer account

Customer storefront authentication uses a dedicated `customer` guard and `Customer` model. Administrative `User` authentication remains independent. Orders may be checked out as guests; authenticated checkouts store `customer_id`. Guest orders may only be claimed during account creation when the e-mail matches and the protected checkout session hash belongs to the same browser session.

## External providers

Payment and shipping implementations are resolved through `PaymentGateway` and `ShippingProvider` contracts using `config/payments.php` and `config/shipping.php`. Payments retain their existing demo driver. Shipping resolves active providers through the Registry, with no automatic fake fallback; tests opt into their own demo fixture.

## Admin operational modules (2026-10)

The administration panel now treats orders as an operational workflow rather than a generic editable CRUD. All status changes go through `TransitionOrderStatusAction`; Filament pages never mutate order status directly. Payment approval uses `HandleApprovedPaymentAction`, which synchronizes payment/order state and records the financial income idempotently.

Order workflow:

`pending -> paid -> processing -> shipped -> delivered`

Pickup workflow:

`pending -> paid -> processing -> ready_for_pickup -> delivered`

`cancelled` is a terminal branch available only before dispatch/readiness.

The finance module is intentionally a lightweight cash-flow ledger, not an ERP. Approved payments create immutable automatic income entries keyed by `payment_id`; manual income/expense entries are editable through their Actions.
