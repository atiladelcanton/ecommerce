# Storefront UI

## Principles

- reflect SABA Máquinas branding;
- commercial and direct, not visually overloaded;
- responsive first;
- accessible controls and keyboard-friendly search;
- fast product discovery.

## Home

Planned sequence:

1. Shared header: logo, wide existing autocomplete search and configured WhatsApp/contact link; second row with Início, Produtos, Categorias, Quem Somos and Contato. Account/cart links are reserved for when those features exist.
2. Rotating hero banners
3. Compact category navigation: 128px cards, registered image with dark overlay, white name and a small arrow; typographic fallback without an image. Grid: 1/2/4/5 columns on mobile/tablet/desktop/wide desktop.
4. Up to 8 featured products; prefer products absent from the displayed promotions, filling with overlaps only when necessary.
5. Up to 8 promotions, only when valid promotional products exist
6. Institutional/WhatsApp callout and footer

Hero-to-category spacing is 56px; subsequent sections use 64px, with 72px before the footer. Home, promotions, related products and `/produtos` share `components/store/product-card.blade.php` and its common CSS. The catalog grid uses 1/2/3/4 columns across mobile/tablet/desktop/wide desktop; filters retain their existing behavior.

## Product detail

- breadcrumb
- image carousel
- product name
- availability
- regular price
- promotional price presentation when valid
- Add to cart
- Quote on WhatsApp
- formatted description, sanitized through `ProductDescription` using a restricted HTML allowlist; legacy plain text retains line breaks
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

## Public navigation and institutional pages

- Desktop uses a compact sticky header in two rows. The category mega menu opens on hover/click, supports keyboard/Escape, and lists only active database categories, linking to `/produtos?categoria={slug}`.
- Shortcuts use existing routes/filters: all products and promotions (`promocao=1`). No featured-products filter currently exists, so no unsupported featured shortcut is displayed.
- Mobile uses logo, search toggle and menu button. The native dialog drawer contains navigation, expandable categories and WhatsApp only when configured; Escape closes the drawer and preserves native focus behavior.
- `/quem-somos` (`store.about`) uses neutral editorial content from `config/storefront.php`. Copy is separate from its view and can later be provided by admin-managed content, without adding persistence now.
- `/contato` (`store.contact`) reads phone, WhatsApp, email and complete/partial address from StoreSettings through `GetContactPageDataAction`. Empty channels are omitted; if none exist, show a neutral empty state. No contact form, mail sending, persistence or simulated confirmation is implemented.

## Customer area

Menu:

- Account
- Orders
- Addresses
- Change password
- Logout

Order detail includes current status, totals, items, shipping/tracking and status timeline.
