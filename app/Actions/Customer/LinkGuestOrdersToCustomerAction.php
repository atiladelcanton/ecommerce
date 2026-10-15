<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Domain\Customer\Models\Customer;
use App\Domain\Order\Models\Order;
use App\Support\BrazilianData;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\DB;

final class LinkGuestOrdersToCustomerAction
{
    public const SESSION_KEY = 'store.customer.claimable_orders';

    public function __construct(private readonly Session $session) {}

    public function execute(Customer $customer): int
    {
        $claimable = $this->session->get(self::SESSION_KEY, []);
        if (! is_array($claimable) || $claimable === []) {
            return 0;
        }

        $pairs = collect($claimable)
            ->filter(fn (mixed $token, mixed $orderId): bool => is_numeric($orderId) && is_string($token) && $token !== '')
            ->mapWithKeys(fn (string $token, mixed $orderId): array => [(int) $orderId => $token]);

        if ($pairs->isEmpty()) {
            $this->session->forget(self::SESSION_KEY);
            return 0;
        }

        $email = mb_strtolower(trim($customer->email));
        $document = BrazilianData::digits($customer->document);

        $linked = DB::transaction(function () use ($pairs, $customer, $email, $document): int {
            $count = 0;
            Order::query()
                ->whereNull('customer_id')
                ->whereIn('id', $pairs->keys())
                ->whereRaw('LOWER(customer_email) = ?', [$email])
                ->where('customer_document', $document)
                ->lockForUpdate()
                ->get(['id', 'checkout_token'])
                ->each(function (Order $order) use ($customer, $pairs, &$count): void {
                    $expectedToken = $pairs->get($order->id);
                    if (! is_string($expectedToken) || ! hash_equals($order->checkout_token, $expectedToken)) {
                        return;
                    }
                    $count += Order::query()->whereKey($order->id)->whereNull('customer_id')->update(['customer_id' => $customer->id]);
                });
            return $count;
        });

        // Capabilities are intentionally one-shot.
        $this->session->forget(self::SESSION_KEY);

        return $linked;
    }
}
