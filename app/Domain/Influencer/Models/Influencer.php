<?php

declare(strict_types=1);

namespace App\Domain\Influencer\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

final class Influencer extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'instagram', 'tiktok', 'pix_key', 'commission_bps', 'notes',
    ];

    protected $hidden = ['password', 'remember_token', 'pix_key', 'notes'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'commission_bps' => 'integer',
            'pix_key' => 'encrypted',
        ];
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(\App\Domain\Coupon\Models\Coupon::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(InfluencerCommission::class);
    }
}
