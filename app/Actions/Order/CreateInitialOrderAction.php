<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\GetCartAction;
use App\Actions\Catalog\ManageOrderInventoryAction;
use App\Actions\Customer\LinkGuestOrdersToCustomerAction;
use App\Actions\Shipping\QuoteShippingAction;
use App\Domain\Cart\CartItem;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Order\OrderTotals;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\Enums\ShippingPricingStatus;
use App\Infrastructure\Cart\SessionCart;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateInitialOrderAction
{
    public function __construct(
        private readonly Session $session,
        private readonly SessionCart $sessionCart,
        private readonly GetCartAction $cart,
        private readonly ClearCartAction $clearCart,
        private readonly ValidateCheckoutData $validate,
        private readonly QuoteShippingAction $shipping,
        private readonly AuthFactory $auth,
    ) {}

    public function execute(array $input, string $token): Order
    {
        return $this->create($input, $token, false);
    }

    public function requestShippingQuote(array $input, string $token): Order
    {
        $input['shipping_option'] = 'manual:pending';

        return $this->create($input, $token, true);
    }

    private function create(array $input, string $token, bool $requestFreight): Order
    {
        if (! Str::isUuid($token)) {
            throw ValidationException::withMessages(['checkout' => 'Reabra o checkout para finalizar seu pedido.']);
        }
        $owner = hash('sha256', $this->session->getId());
        try {
            $order = Cache::lock('checkout:'.$token, 30)->block(5, function () use ($input, $token, $owner, $requestFreight): Order {
                $existing = Order::query()->where('checkout_token', $token)->first();
                if ($existing) {
                    abort_unless(hash_equals($existing->checkout_session_hash, $owner), 404);

                    return $existing;
                }
                if ($this->session->get(GetCheckoutTokenAction::SESSION_KEY) !== $token) {
                    throw ValidationException::withMessages(['checkout' => 'Reabra o checkout para finalizar seu pedido.']);
                }
                $data = $this->validate->execute($input);

                return DB::transaction(function () use ($data, $token, $owner, $requestFreight): Order {
                    $stored = $this->sessionCart->items();
                    if ($stored === []) {
                        throw ValidationException::withMessages(['cart' => 'Seu carrinho está vazio. Adicione produtos antes de finalizar.']);
                    }
                    $ids = array_unique(array_map(fn (CartItem $item): int => $item->productId, $stored));
                    // Lock catalog rows before reading prices/variants for the immutable snapshots.
                    Product::query()->whereKey($ids)->orderBy('id')->lockForUpdate()->get(['id']);
                    ProductVariant::query()->whereIn('product_id', $ids)->orderBy('id')->lockForUpdate()->get(['id']);
                    $cart = $this->cart->summarize($stored, validateStock: true);
                    if (count($cart['items']) !== count($stored)) {
                        throw ValidationException::withMessages(['cart' => 'Alguns produtos não estão mais disponíveis. Revise seu carrinho.']);
                    }
                    try {
                        $options = $this->shipping->execute($cart, $data['postal_code']);
                    } catch (ValidationException $exception) {
                        if (! $requestFreight || ! isset($exception->errors()['shipping_option'])) {
                            throw $exception;
                        }
                        $options = [];
                    }
                    $shipping = collect($options)->first(fn (ShippingOption $option): bool => $option->id === $data['shipping_option']);
                    if ($requestFreight) {
                        if (collect($options)->contains(fn (ShippingOption $option): bool => $option->fulfillmentMode !== 'pickup')) {
                            throw ValidationException::withMessages(['shipping_option' => 'Encontramos opções de entrega. Selecione uma delas para continuar.']);
                        }
                        $shipping = null;
                    } elseif (! $shipping) {
                        throw ValidationException::withMessages(['shipping_option' => 'Selecione uma forma de entrega disponível.']);
                    }
                    $totals = $shipping ? new OrderTotals($cart['subtotal'], $shipping->price) : null;
                    $order = Order::query()->create([
                        'customer_id' => $this->auth->guard('customer')->id(),
                        'checkout_token' => $token, 'checkout_session_hash' => $owner,
                        'customer_name' => $data['name'], 'customer_email' => $data['email'],
                        'customer_phone' => $data['phone'], 'customer_document' => $data['document'],
                        'status' => OrderStatus::PENDING, 'payment_status' => PaymentStatus::PENDING,
                        'subtotal_cents' => $cart['subtotal'], 'shipping_cents' => $totals?->shipping,
                        'discount_cents' => 0, 'total_cents' => $totals?->total,
                        'shipping_pricing_status' => $requestFreight ? ShippingPricingStatus::AWAITING_QUOTE : null,
                        'shipping_option_id' => $shipping?->id ?? 'manual:pending',
                        'shipping_provider' => $shipping?->provider ?? 'manual',
                        'shipping_integration_id' => $shipping?->integrationId,
                        'shipping_environment' => $shipping?->environment,
                        'shipping_service_id' => $shipping?->serviceExternalId,
                        'shipping_name' => $shipping?->name ?? 'Entrega sob consulta',
                        'shipping_description' => $shipping?->description ?? 'Aguardando cotação do frete',
                        'shipping_quote_snapshot' => $shipping?->snapshot() ?? ['provider' => 'manual', 'fulfillment_mode' => 'manual', 'pricing_status' => 'awaiting_quote'],
                        'carrier' => $shipping?->carrierName,
                    ]);
                    $prefix = strtoupper(trim((string) config('storefront.order_prefix', 'SABA'))) ?: 'SABA';
                    $order->update(['number' => $prefix.'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT)]);
                    foreach ($cart['items'] as $line) {
                        $order->items()->create([
                            'product_id' => $line['item']->productId, 'product_variant_id' => $line['item']->productVariantId,
                            'product_name' => $line['product']->name, 'product_sku' => $line['offer']->sku,
                            'variant_name' => $line['variant']?->name, 'variant_value' => $line['variant']?->value,
                            'variant_attributes_snapshot' => $line['offer']->attributes, 'shipping_measurements_snapshot' => $line['offer']->measurements,
                            'regular_unit_price' => $line['offer']->pricing->regularPrice, 'unit_price' => $line['unitPrice'],
                            'quantity' => $line['item']->quantity, 'total' => $line['subtotal'],
                        ]);
                    }
                    $order->address()->create(array_intersect_key($data, array_flip([
                        'postal_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state',
                    ])));
                    $order->statusHistories()->create([
                        'from_status' => null, 'to_status' => OrderStatus::PENDING, 'description' => 'Pedido criado.',
                    ]);
                    app(ManageOrderInventoryAction::class)->reserve($order);
                    // External checkout must start after the database transaction has committed.

                    return $order;
                }, 3);
            });
        } catch (UniqueConstraintViolationException $exception) {
            // The database uniqueness constraint is the final backstop if a lock expires.
            $order = Order::query()->where('checkout_token', $token)->where('checkout_session_hash', $owner)->first();
            if (! $order) {
                throw $exception;
            }
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['checkout' => 'Seu pedido está sendo processado. Aguarde e tente novamente.']);
        }

        if (! $requestFreight && $order->payment_status !== PaymentStatus::APPROVED) {
            $order = app(PayOrderAction::class)->execute($order);
        }

        // Keep a server-side session capability for a guest order so a customer who
        // creates an account immediately after checkout can claim only orders created
        // by this browser session. This is intentionally independent from the PHP
        // session ID, which may be regenerated between requests.
        if ($order->customer_id === null) {
            $claimable = $this->session->get(LinkGuestOrdersToCustomerAction::SESSION_KEY, []);
            if (! is_array($claimable)) {
                $claimable = [];
            }
            $claimable[$order->id] = $order->checkout_token;
            $this->session->put(LinkGuestOrdersToCustomerAction::SESSION_KEY, $claimable);
        }

        // Never clear a newer cart when an old successful submission is replayed.
        if ($this->session->get(GetCheckoutTokenAction::SESSION_KEY) === $token) {
            $this->clearCart->execute();
            $this->session->forget(GetCheckoutTokenAction::SESSION_KEY);
        }

        return $order;
    }
}
