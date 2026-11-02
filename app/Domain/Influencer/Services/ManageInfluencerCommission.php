<?php

declare(strict_types=1);

namespace App\Domain\Influencer\Services;

use App\Domain\Influencer\Models\InfluencerCommission;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ManageInfluencerCommission
{
    public function advance(InfluencerCommission $commission, string $target, int $actor, ?string $note = null): void
    {
        // A visible Filament action alone is not sufficient protection for payout changes.
        $operator = \App\Models\User::query()->with('adminGroup')->find($actor);
        abort_unless(auth('web')->id() === $actor && $operator?->adminGroup?->slug === 'administrador', 403);

        DB::transaction(function () use ($commission, $target, $actor, $note): void {
            $row = InfluencerCommission::query()->lockForUpdate()->findOrFail($commission->id);
            $order = $row->order()->lockForUpdate()->firstOrFail();
            $current = $row->status;
            $valid = match ($target) {
                'approved' => $current === 'forecast' && $order->payment_status === PaymentStatus::APPROVED && $order->status !== OrderStatus::CANCELLED,
                'paid' => $current === 'approved' && filled($note) && $order->payment_status === PaymentStatus::APPROVED && $order->status !== OrderStatus::CANCELLED,
                'reversed' => in_array($current, ['review', 'forecast', 'approved'], true) && filled($note),
                default => false,
            };
            if (! $valid) {
                throw ValidationException::withMessages(['status' => 'A comissão não permite esta transição. Confira pagamento, pedido e situação atual.']);
            }
            $extra = match ($target) {
                'approved' => ['approved_at' => now()],
                'paid' => ['paid_at' => now()],
                'reversed' => ['reversed_at' => now()],
            };
            $row->update(['status' => $target, 'reviewed_by' => $actor, 'notes' => $note] + $extra);
        }, 3);
    }
}
