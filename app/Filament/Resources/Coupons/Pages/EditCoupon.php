<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons\Pages;

use App\Actions\Coupon\SaveCouponAction;
use App\Domain\Coupon\Models\Coupon;
use App\Filament\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Coupon $coupon */
        $coupon = $this->getRecord();
        $data['percentage_value'] = $coupon->discount_type === 'percentage' ? $coupon->value : null;
        $data['fixed_value_cents'] = $coupon->discount_type === 'fixed' ? $coupon->value : null;
        $data['product_ids'] = $coupon->products()->pluck('products.id')->all();
        $data['category_ids'] = $coupon->categories()->pluck('categories.id')->all();
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(SaveCouponAction::class)->execute($data, $record);
    }
}
