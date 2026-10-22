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
        return app(SaveCouponAction::class)->execute($data);
    }
}
