<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons\Pages;

use App\Actions\Coupon\SaveCouponAction;
use App\Filament\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $data['percentage_value'] = $record->discount_type === 'percentage' ? $record->value : null;
        $data['fixed_value_cents'] = $record->discount_type === 'fixed' ? $record->value : null;
        $data['category_ids'] = $record->categories()->pluck('categories.id')->all();
        $data['product_ids'] = $record->products()->pluck('products.id')->all();
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['value'] = $data['discount_type'] === 'percentage' ? $data['percentage_value'] : $data['fixed_value_cents'];
        return app(SaveCouponAction::class)->execute($data, $record);
    }
}
