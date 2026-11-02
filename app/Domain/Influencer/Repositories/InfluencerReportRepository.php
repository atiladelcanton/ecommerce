<?php

declare(strict_types=1);

namespace App\Domain\Influencer\Repositories;

use App\Domain\Influencer\Models\Influencer;
use App\Domain\Influencer\Models\InfluencerCommission;
use App\Domain\Order\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Read-only reporting; all queries scoped by authenticated influencer, never an input ID. */
final class InfluencerReportRepository
{
    public function overview(Influencer $influencer): array
    {
        $couponIds = $influencer->coupons()->pluck('id');
        $orders = Order::query()->whereIn('coupon_id', $couponIds);
        $totals = InfluencerCommission::query()->where('influencer_id', $influencer->getKey())
            ->selectRaw('status, SUM(amount_cents) as amount')->groupBy('status')->pluck('amount', 'status');

        return [
            'coupon_count' => $couponIds->count(),
            'order_count' => (clone $orders)->count(),
            'paid_order_count' => (clone $orders)->where('payment_status', 'approved')->count(),
            'pending_order_count' => (clone $orders)->where('status', 'pending')->count(),
            'cancelled_order_count' => (clone $orders)->where('status', 'cancelled')->count(),
            'confirmed_sales_cents' => (int) (clone $orders)->where('payment_status', 'approved')->selectRaw('SUM(subtotal_cents - discount_cents) AS net_sales')->first()?->net_sales,
            'forecast_cents' => (int) ($totals['forecast'] ?? 0),
            'approved_cents' => (int) ($totals['approved'] ?? 0),
            'paid_cents' => (int) ($totals['paid'] ?? 0),
            'review_cents' => (int) ($totals['review'] ?? 0),
        ];
    }

    public function coupons(Influencer $influencer): Collection
    {
        return $influencer->coupons()->withCount(['usages as uses_count' => fn ($q) => $q->where('status', 'consumed')])
            ->orderByDesc('is_active')->orderBy('code')->get();
    }

    public function orders(Influencer $influencer, int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()->whereHas('coupon', fn ($q) => $q->where('influencer_id', $influencer->id))
            ->select(['id', 'number', 'coupon_id', 'created_at', 'status', 'payment_status', 'subtotal_cents', 'discount_cents'])
            ->with(['coupon:id,code,influencer_id'])->latest('id')->paginate($perPage);
    }

    public function commissions(Influencer $influencer, int $perPage = 15): LengthAwarePaginator
    {
        return InfluencerCommission::query()->where('influencer_id', $influencer->id)
            ->with(['order:id,number', 'coupon:id,code'])->latest('id')->paginate($perPage);
    }

    public function paidCommissions(Influencer $influencer, int $perPage = 15): LengthAwarePaginator
    {
        return InfluencerCommission::query()->where('influencer_id', $influencer->id)
            ->where('status', 'paid')->whereNotNull('paid_at')
            ->with(['order:id,number'])->orderByDesc('paid_at')->paginate($perPage);
    }

    public function recentCommissions(Influencer $influencer, int $limit = 5): Collection
    {
        return InfluencerCommission::query()->where('influencer_id', $influencer->id)
            ->with(['order:id,number'])->latest('id')->limit($limit)->get();
    }
}
