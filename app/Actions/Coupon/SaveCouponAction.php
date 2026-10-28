<?php

declare(strict_types=1);

namespace App\Actions\Coupon;

use App\Domain\Coupon\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class SaveCouponAction
{
    public function execute(array $input, ?Coupon $existing = null): Coupon
    {
        $input['code'] = QuoteCouponAction::normalize((string) ($input['code'] ?? ''));
        $input['allow_sale_items'] = (bool) ($input['allow_sale_items'] ?? false);
        $input['is_active'] = (bool) ($input['is_active'] ?? false);
        $input['category_ids'] = $input['category_ids'] ?? [];
        $input['product_ids'] = $input['product_ids'] ?? [];
        $data = Validator::make($input, [
            'code' => ['required', 'string', 'between:3,64', 'regex:/^[A-Z0-9_-]+$/D', Rule::unique('coupons', 'code')->ignore($existing?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'integer', 'min:1'],
            'scope' => ['required', Rule::in(['all', 'categories', 'products'])],
            'category_ids' => ['array'],
            'category_ids.*' => ['integer', 'distinct', 'exists:categories,id'],
            'product_ids' => ['array'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'minimum_subtotal_cents' => ['nullable', 'integer', 'min:0'],
            'maximum_discount_cents' => ['nullable', 'integer', 'min:1'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'allow_sale_items' => ['boolean'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ])->after(function ($validator) use ($input): void {
            if (($input['discount_type'] ?? null) === 'percentage' && (int) ($input['value'] ?? 0) > 100) {
                $validator->errors()->add('value', 'O percentual deve estar entre 1 e 100.');
            }
            foreach (['categories' => 'category_ids', 'products' => 'product_ids'] as $scope => $field) {
                if (($input['scope'] ?? null) === $scope && empty($input[$field])) {
                    $validator->errors()->add($field, 'Selecione ao menos um item para o alcance do cupom.');
                }
            }
        })->validate();

        return DB::transaction(function () use ($data, $existing): Coupon {
            $coupon = $existing ? Coupon::query()->lockForUpdate()->findOrFail($existing->id) : new Coupon;
            if ($coupon->exists) {
                $claims = $coupon->claims()->count();
                if ($claims > 0 && $data['code'] !== $coupon->code) {
                    throw ValidationException::withMessages(['code' => 'O código de um cupom reservado ou utilizado não pode ser alterado.']);
                }
                if (($data['usage_limit'] ?? null) !== null && $data['usage_limit'] < $claims) {
                    throw ValidationException::withMessages(['usage_limit' => 'O limite não pode ser inferior às reservas e utilizações existentes.']);
                }
            }
            $data['usage_limit'] = $data['usage_limit'] ?? null;
            $attrs = array_diff_key($data, array_flip(['category_ids', 'product_ids']));
            $attrs['minimum_subtotal_cents'] = $attrs['minimum_subtotal_cents'] ?? null;
            $coupon->fill($attrs)->save();
            $coupon->categories()->sync($data['scope'] === 'categories' ? $data['category_ids'] : []);
            $coupon->products()->sync($data['scope'] === 'products' ? $data['product_ids'] : []);
            return $coupon->refresh();
        }, 3);
    }
}
