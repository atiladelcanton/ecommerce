<?php

declare(strict_types=1);

namespace App\Actions\Coupon;

use App\Domain\Coupon\Models\Coupon;
use Illuminate\Validation\ValidationException;

/** Calculates a typed discount from backend-resolved line prices, never browser totals. */
final class QuoteCouponAction
{
    public static function normalize(string $code): string
    {
        return mb_strtoupper(trim($code));
    }

    /** @return array{coupon: Coupon, discount: int} */
    public function execute(string $code, array $lines, int $subtotal): array
    {
        $coupon = Coupon::query()->where('code', self::normalize($code))->first();
        if (! $coupon) {
            throw ValidationException::withMessages(['coupon' => 'Cupom não encontrado.']);
        }
        return ['coupon' => $coupon, 'discount' => $this->calculate($coupon, $lines, $subtotal)];
    }

    public function calculate(Coupon $coupon, array $lines, int $subtotal): int
    {
        if (! $coupon->is_active || ($coupon->starts_at && now()->lt($coupon->starts_at)) || ($coupon->ends_at && now()->gt($coupon->ends_at))) {
            throw ValidationException::withMessages(['coupon' => 'Este cupom está inativo ou fora do prazo de validade.']);
        }
        if ($coupon->minimum_subtotal_cents !== null && $subtotal < $coupon->minimum_subtotal_cents) {
            throw ValidationException::withMessages(['coupon' => 'O valor mínimo de compra para este cupom ainda não foi atingido.']);
        }
        $eligible = 0;
        $ids = $coupon->scope === 'products' ? $coupon->products()->pluck('products.id')->all() : [];
        $categories = $coupon->scope === 'categories' ? $coupon->categories()->pluck('categories.id')->all() : [];
        foreach ($lines as $line) {
            $product = $line['product'];
            if (($coupon->scope === 'products' && ! in_array($product->id, $ids, true)) ||
                ($coupon->scope === 'categories' && ! in_array($product->category_id, $categories, true))) {
                continue;
            }
            if (! $coupon->allow_sale_items && $line['offer']->pricing->hasPromotion()) {
                continue;
            }
            $eligible += (int) $line['subtotal'];
        }
        if ($eligible < 1) {
            throw ValidationException::withMessages(['coupon' => 'Nenhum produto do carrinho participa deste cupom.']);
        }
        if ($coupon->discount_type === 'percentage') {
            if ($coupon->value < 1 || $coupon->value > 100) {
                throw ValidationException::withMessages(['coupon' => 'Cupom com percentual inválido.']);
            }
            $discount = intdiv($eligible, 100) * $coupon->value + intdiv(($eligible % 100) * $coupon->value, 100);
        } else {
            $discount = min($eligible, $coupon->value);
        }
        if ($coupon->maximum_discount_cents !== null) {
            $discount = min($discount, $coupon->maximum_discount_cents);
        }
        // Hosted gateways require a payable positive amount and cannot accept zero-value lines.
        if ($discount < 1 || $discount >= $subtotal) {
            throw ValidationException::withMessages(['coupon' => 'Este cupom não pode ser aplicado ao valor atual do carrinho.']);
        }
        return $discount;
    }
}
