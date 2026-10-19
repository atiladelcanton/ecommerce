@php
    $value = $status instanceof \App\Domain\Order\Enums\OrderStatus ? $status->value : (string) $status;
@endphp

@switch($value)
    @case('pending')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M6 3.75h9.75L19.5 7.5v12.75H6z" />
            <path d="M15.75 3.75V7.5h3.75M9 11h7.5M9 14.5h7.5M9 18h4.5" />
        </svg>
        @break

    @case('paid')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="5" width="18" height="14" rx="2.5" />
            <path d="M3 9h18M7 15h4" />
            <path d="m14.5 14.5 1.5 1.5 3-3" />
        </svg>
        @break

    @case('processing')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m4.5 7.5 7.5-4 7.5 4-7.5 4z" />
            <path d="M4.5 7.5v9l7.5 4 7.5-4v-9M12 11.5v9" />
            <path d="M8.25 5.5 15.75 9.5" />
        </svg>
        @break

    @case('ready_for_shipping')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m4.5 7.5 7.5-4 7.5 4-7.5 4z" />
            <path d="M4.5 7.5v9l7.5 4 7.5-4v-9M12 11.5v9" />
            <path d="m14.5 15.5 1.6 1.6 3.2-3.2" />
        </svg>
        @break

    @case('ready_for_pickup')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 8.5h16l-1.2-4H5.2z" />
            <path d="M5.5 8.5v11h13v-11M9 19.5v-6h6v6" />
            <path d="M3.5 8.5c0 1.2 1 2.2 2.2 2.2S8 9.7 8 8.5c0 1.2 1 2.2 2.2 2.2s2.3-1 2.3-2.2c0 1.2 1 2.2 2.2 2.2S17 9.7 17 8.5c0 1.2 1 2.2 2.2 2.2s2.3-1 2.3-2.2" />
        </svg>
        @break

    @case('shipped')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 6.5h11v10H3zM14 10h3.5l3 3v3.5H14z" />
            <path d="M6.5 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM17.5 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
            <path d="M14 13h6.5" />
        </svg>
        @break

    @case('delivered')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3.5 10.5 12 3.5l8.5 7" />
            <path d="M5.5 9.5v10h13v-10" />
            <path d="m9 14.5 2 2 4-4" />
        </svg>
        @break

    @case('cancelled')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="8.5" />
            <path d="m9 9 6 6M15 9l-6 6" />
        </svg>
        @break

    @default
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="8.5" />
            <path d="M12 8v4.5l3 2" />
        </svg>
@endswitch
