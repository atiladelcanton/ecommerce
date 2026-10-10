# Storefront UI

## Principles

- reflect SABA Máquinas branding;
- commercial and direct, not visually overloaded;
- responsive first;
- accessible controls and keyboard-friendly search;
- fast product discovery.

## Home

Planned sequence:

1. Shared header: logo, wide existing autocomplete search, configured WhatsApp/contact link and cart icon linking to `/carrinho` with a reactive total-unit badge; second row with Início, Produtos, Categorias, Quem Somos and Contato. Account links are reserved for when those features exist.
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

The color selector is shared by the Livewire “Adicionar ao carrinho” action and the WhatsApp quote. Selecting a color is required for products with Cor variants. Successful addition dispatches a global success toast and refreshes the header badge without leaving the product page. Validation failures keep the existing server-side validation and dispatch a warning/error toast.

## Storefront notifications

The public layout includes `components/store/toast-container.blade.php`, powered by the existing Alpine instance and `resources/js/store-toasts.js`. Any Livewire component can dispatch `toast` with `type`, `title`, `message` and optional `actionLabel`/`actionUrl`. Supported types are `success`, `error`, `warning` and `info`; only the icon and discreet progress bar use the type color. Text is rendered with `x-text`; action URLs must belong to the storefront origin.

White cards sit 96px from the top and 24px from the right on desktop, with a 380px width. Mobile uses 12px top/side margins. New notifications enter at the top, with a maximum of three; a fourth replaces the oldest. Each expires after 4 seconds, pausing while hovered or while its controls have keyboard focus. A close button dismisses manually. Enter/exit transitions use 300ms/200ms and a 20px horizontal translation, respecting reduced-motion preferences. Polite live announcements serve success/warning/info; errors use an assertive live region. There are no external notification dependencies or Filament notifications in the storefront.

## Public cart

`/carrinho` is available without login. Desktop shows a full-width product table and, on wide screens, a side summary; mobile presents each table row as a compact product card and stacks the summary below. Rows show the primary photo (or image fallback), catalog name, optional color, current unit price, Livewire minus/quantity/plus control, subtotal and explicit removal.

The summary shows subtotal, “Frete: Calculado no checkout” and total parcial. “Continuar para o checkout” points to `/checkout`. “Continuar comprando” returns to `/produtos`. An empty cart shows “Seu carrinho está vazio.” and “Ver produtos”. Clearing all items uses Livewire confirmation. Controls prevent decrementing below 1 and disable while requests are pending.

## Guest checkout and initial order confirmation

`/checkout` uses a thin Livewire component and the shared public layout. Customer, delivery and demo-payment fields are grouped into bordered white cards, with a responsive two-column field grid. The order summary sits alongside the form on wide screens and stacks below on mobile. Choosing an option updates server-calculated shipping and total. A visible “Pagamento de demonstração” section explains that no charge is made. “Finalizar pedido de teste” disables during submission; field errors and shipping failures are accessible and do not invent a free fallback. Empty checkout uses the existing empty-cart state.

Success redirects to `/pedido/{number}/confirmacao`. The page presents the readable order number, immutable item/price/color snapshots, customer and delivery data, chosen delivery method and approved demonstration payment. Only the originating session can access it; responses use private/no-store cache control. Local/development additionally displays “Este pedido utilizou pagamento simulado.” No account screen, real gateway flow, order admin or real shipping quotation is introduced.

The CEP input uses the existing Alpine mask plugin (`00000-000`) and `wire:model.blur`. Leaving the input triggers lookup only for valid eight-digit CEPs; incomplete values produce a field validation message without an external request. A small “Buscando endereço...” status appears during lookup. Success fills street, neighborhood, city and state, leaving the number unchanged. A useful provider complement fills an empty complement only. All address fields remain editable. Not-found/service-failure messages appear below the CEP input; they do not prevent manually completing checkout. Results are cached for 24 hours; temporary failures are not cached.

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
