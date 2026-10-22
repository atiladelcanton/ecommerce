<?php

declare(strict_types=1);

namespace App\Domain\Coupon\Models;

use Illuminate\Database\Eloquent\Model;

final class CouponClaim extends Model
{
    protected $fillable = ['coupon_id', 'coupon_usage_id', 'document_hash'];
}
