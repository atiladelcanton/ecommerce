<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Models;

use Illuminate\Database\Eloquent\Model;

final class ShippingIntegration extends Model
{
    protected $hidden = ['credentials', 'configuration', 'access_token', 'refresh_token'];

    protected $fillable = [
        'provider',
        'is_enabled',
        'environment',
        'sort_order',
        'configuration',
        'credentials',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'connected_at',
        'last_tested_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
            'configuration' => 'encrypted:array',
            'credentials' => 'encrypted:array',
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'connected_at' => 'datetime',
            'last_tested_at' => 'datetime',
        ];
    }

    public static function forProvider(string $provider): self
    {
        return self::query()->firstOrCreate(
            ['provider' => $provider],
            [
                'is_enabled' => false,
                'environment' => 'sandbox',
                'sort_order' => 100,
                'configuration' => [],
                'credentials' => [],
            ],
        );
    }
}
