<?php

declare(strict_types=1);

namespace App\Actions\Coupon;

use App\Domain\Coupon\Models\Coupon;
use Illuminate\Validation\ValidationException;

final class ValidateCouponAction
{
    /** @return array{coupon: Coupon, discount: int} */
    public function execute(string $code, int $subtotal, bool $lock = false): array
    {
        $normalized = strtoupper(trim($code));
        if ($normalized === '' || strlen($normalized) > 40) {
            throw ValidationException::withMessages(['coupon' => 'Informe um código de cupom válido.']);
        }
        $query = Coupon::query()->where('code', $normalized);
        $coupon = $lock ? $query->lockForUpdate()->first() : $query->first();
        if (! $coupon) {
            throw ValidationException::withMessages(['coupon' => 'Cupom não encontrado. Confira o código informado.']);
        }

        return ['coupon' => $coupon, 'discount' => $this->calculate($coupon, $subtotal)];
    }

    public function calculate(Coupon $coupon, int $subtotal): int
    {
        if (! $coupon->is_active) {
            throw ValidationException::withMessages(['coupon' => 'Este cupom está desativado.']);
        }
        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw ValidationException::withMessages(['coupon' => 'Este cupom ainda não está vigente.']);
        }
        if ($coupon->ends_at && $coupon->ends_at->isPast()) {
            throw ValidationException::withMessages(['coupon' => 'Este cupom expirou.']);
        }
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['coupon' => 'Este cupom atingiu o limite de utilizações.']);
        }
        if ($subtotal < $coupon->min_subtotal_cents) {
            throw ValidationException::withMessages(['coupon' => 'O pedido não atingiu o valor mínimo para este cupom.']);
        }
        // Integer-only arithmetic, including very large subtotals.
        $discount = $coupon->discount_type === 'percentage'
            ? intdiv($subtotal, 100) * $coupon->percentage + intdiv(($subtotal % 100) * $coupon->percentage, 100)
            : min($subtotal, $coupon->amount_cents ?? 0);
        if ($discount <= 0) {
            throw ValidationException::withMessages(['coupon' => 'O subtotal é insuficiente para aplicar este desconto.']);
        }

        return $discount;
    }
}
