# Architecture

## Goal

Keep SABA Máquinas easy to understand, test and evolve while avoiding unnecessary enterprise ceremony.

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

### Cart
Guest/authenticated shopping cart and cart items.

### Order
Order creation, immutable line snapshots, status history and fulfillment state.

### Payment
Gateway abstraction, payment attempts, external events and webhook processing.

### Shipping
Quotation abstraction, selected shipment and tracking data.

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
   -> ShippingProvider contract
      -> FakeShippingProvider
      -> MelhorEnvioShippingProvider
```

## Money

Use integer cents internally and in the database whenever possible.

Example: R$ 12.999,90 = `1299990` cents.

Create a Money value object only when enough rules exist to justify it; do not add it merely for aesthetics.

## Transactions

Use database transactions for order creation, payment approval side effects, refunds/cancellations that affect finance, and other multi-record critical operations.

## Events

Domain/application events are welcome when they decouple real side effects. Do not create events for simple local method calls.
