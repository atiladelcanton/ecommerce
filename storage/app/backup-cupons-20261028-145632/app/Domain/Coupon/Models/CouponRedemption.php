<?php

declare(strict_types=1);

namespace App\Domain\Coupon\Models;

use Illuminate\Database\Eloquent\Model;

final class CouponRedemption extends Model
{
    protected $fillable = ['coupon_id', 'order_id', 'discount_cents'];
}
