# Storefront UI

## Principles

- reflect SABA Máquinas branding;
- commercial and direct, not visually overloaded;
- responsive first;
- accessible controls and keyboard-friendly search;
- fast product discovery.

## Home

Planned sequence:

1. Header: logo, category navigation, autocomplete search, account, cart
2. Rotating hero banners
3. Category navigation/list (textual; no category image)
4. Featured products
5. Promotions section only when valid promotional products exist
6. General product/category discovery
7. Institutional/WhatsApp callout when useful
8. Footer

## Product detail

- breadcrumb
- image carousel
- product name
- availability
- regular price
- promotional price presentation when valid
- Add to cart
- Quote on WhatsApp
- description
- optional YouTube demonstration area
- related products

When discounted, show the current promotional price prominently and the original regular price smaller with strike-through styling.

## Search autocomplete

- Livewire component
- ~300ms debounce as starting point
- bounded suggestions (e.g. 5 products + a small number of categories)
- keyboard navigation
- “see all results” action
- indexed database queries
- no unbounded `%term%` scans across large text columns

## Customer area

Menu:

- Account
- Orders
- Addresses
- Change password
- Logout

Order detail includes current status, totals, items, shipping/tracking and status timeline.
