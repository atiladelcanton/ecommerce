<?php

declare(strict_types=1);

namespace App\Actions\Coupon;

use App\Domain\Coupon\Models\Coupon;
use App\Domain\Coupon\Models\CouponClaim;
use App\Domain\Coupon\Models\CouponUsage;
use App\Domain\Order\Models\Order;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

/** Invoke inside an order transaction. Lock order before coupon in every code path. */
final class ManageCouponUsageAction
{
    public function documentHash(string $document): string
    {
        $normalized = preg_replace('/\D/', '', $document);
        if (! in_array(strlen($normalized), [11, 14], true)) {
            throw ValidationException::withMessages(['document' => 'Informe um CPF ou CNPJ válido para utilizar o cupom.']);
        }
        return hash_hmac('sha256', $normalized, (string) config('app.key'));
    }

    public function reserve(Order $order, Coupon $coupon, int $discount): void
    {
        $hash = $this->documentHash($order->customer_document);
        $lockedCoupon = Coupon::query()->lockForUpdate()->findOrFail($coupon->id);
        if (! $lockedCoupon->is_active || ($lockedCoupon->starts_at && now()->lt($lockedCoupon->starts_at))
            || ($lockedCoupon->ends_at && now()->gt($lockedCoupon->ends_at))) {
            throw ValidationException::withMessages(['coupon' => 'Este cupom está inativo ou fora do prazo de validade.']);
        }
        $existing = CouponUsage::query()->where('order_id', $order->id)->first();
        if ($existing && $existing->status === 'consumed') {
            return;
        }
        if ($existing && $existing->coupon_id !== $lockedCoupon->id) {
            throw ValidationException::withMessages(['coupon' => 'O cupom do pedido não pode ser substituído.']);
        }
        if ($existing && $existing->status === 'reserved') {
            return;
        }
        if ($lockedCoupon->claims()->where('document_hash', $hash)->exists()) {
            throw ValidationException::withMessages(['coupon' => 'Este CPF/CNPJ já utilizou ou possui uma compra pendente com este cupom.']);
        }
        if ($lockedCoupon->usage_limit !== null && $lockedCoupon->claims()->count() >= $lockedCoupon->usage_limit) {
            throw ValidationException::withMessages(['coupon' => 'Este cupom atingiu seu limite de utilizações.']);
        }
        if ($existing) {
            // Retrying a failed payment must be explicit and still respect validity.
            $existing->update(['status' => 'reserved', 'released_at' => null]);
            $usage = $existing;
        } else {
            $usage = CouponUsage::query()->create([
                'coupon_id' => $lockedCoupon->id, 'order_id' => $order->id, 'document_hash' => $hash,
                'status' => 'reserved', 'discount_cents' => $discount,
            ]);
        }
        try {
            CouponClaim::query()->create(['coupon_id' => $lockedCoupon->id, 'coupon_usage_id' => $usage->id, 'document_hash' => $hash]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['coupon' => 'Este CPF/CNPJ já utilizou este cupom.']);
        }
    }

    public function consume(Order $order): void
    {
        $usage = CouponUsage::query()->where('order_id', $order->id)->first();
        if (! $usage || $usage->status === 'consumed') {
            return;
        }
        if ($usage->status === 'released') {
            // A late payment may arrive after a terminal notification released the
            // benefit. Do not silently approve the same person's second discount.
            $coupon = Coupon::query()->lockForUpdate()->findOrFail($usage->coupon_id);
            if ($coupon->claims()->where('document_hash', $usage->document_hash)->exists()
                || ($coupon->usage_limit !== null && $coupon->claims()->count() >= $coupon->usage_limit)) {
                throw ValidationException::withMessages(['coupon' => 'Pagamento tardio de cupom já utilizado: revisão manual necessária.']);
            }
            CouponClaim::query()->create([
                'coupon_id' => $usage->coupon_id, 'coupon_usage_id' => $usage->id,
                'document_hash' => $usage->document_hash,
            ]);
        }
        $usage->update(['status' => 'consumed', 'consumed_at' => now(), 'released_at' => null]);
        // Consumed claim is permanent even if the commercial order is later canceled/refunded.
    }

    public function release(Order $order): void
    {
        $usage = CouponUsage::query()->where('order_id', $order->id)->first();
        if ($usage && $usage->status === 'reserved') {
            CouponClaim::query()->where('coupon_usage_id', $usage->id)->delete();
            $usage->update(['status' => 'released', 'released_at' => now()]);
        }
    }
}
