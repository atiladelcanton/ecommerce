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
    public function execute(array $data, ?Coupon $coupon = null): Coupon
    {
        $data['code'] = QuoteCouponAction::normalize((string) ($data['code'] ?? ''));
        $data['product_ids'] ??= [];
        $data['category_ids'] ??= [];
        $data = Validator::make($data, [
            'code' => ['required', 'regex:/\A[A-Z0-9_-]{3,64}\z/', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'percentage_value' => ['required_if:discount_type,percentage', 'nullable', 'integer', 'between:1,100'],
            'fixed_value_cents' => ['required_if:discount_type,fixed', 'nullable', 'integer', 'min:1'],
            'scope' => ['required', Rule::in(['all', 'categories', 'products'])],
            'product_ids' => ['array'], 'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'category_ids' => ['array'], 'category_ids.*' => ['integer', 'distinct', 'exists:categories,id'],
            'minimum_subtotal_cents' => ['nullable', 'integer', 'min:1'],
            'maximum_discount_cents' => ['nullable', 'integer', 'min:1'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'], 'allow_sale_items' => ['boolean'],
            'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], [
            'code.unique' => 'Já existe um cupom com este código.',
            'code.regex' => 'Utilize 3 a 64 letras, números, hífens ou sublinhados.',
        ])->validate();
        if ($data['scope'] === 'products' && $data['product_ids'] === []) {
            throw ValidationException::withMessages(['product_ids' => 'Escolha pelo menos um produto para este cupom.']);
        }
        if ($data['scope'] === 'categories' && $data['category_ids'] === []) {
            throw ValidationException::withMessages(['category_ids' => 'Escolha pelo menos uma categoria para este cupom.']);
        }
        return DB::transaction(function () use ($data, $coupon): Coupon {
            $values = collect($data)->only([
                'code', 'description', 'discount_type', 'scope', 'minimum_subtotal_cents', 'maximum_discount_cents',
                'usage_limit', 'allow_sale_items', 'is_active', 'starts_at', 'ends_at',
            ])->all();
            $values['value'] = $data['discount_type'] === 'percentage' ? $data['percentage_value'] : $data['fixed_value_cents'];
            $saved = $coupon ?? new Coupon;
            $saved->fill($values)->save();
            $saved->products()->sync($data['scope'] === 'products' ? $data['product_ids'] : []);
            $saved->categories()->sync($data['scope'] === 'categories' ? $data['category_ids'] : []);
            return $saved->refresh();
        });
    }
}
