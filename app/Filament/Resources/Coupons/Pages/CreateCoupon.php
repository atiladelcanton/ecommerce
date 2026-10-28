<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons\Pages;

use App\Actions\Coupon\SaveCouponAction;
use App\Filament\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['value'] = $data['discount_type'] === 'percentage' ? $data['percentage_value'] : $data['fixed_value_cents'];
        return app(SaveCouponAction::class)->execute($data);
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
