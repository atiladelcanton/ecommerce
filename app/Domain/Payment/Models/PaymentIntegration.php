<?php

declare(strict_types=1);

namespace App\Domain\Payment\Models;

use Illuminate\Database\Eloquent\Model;

final class PaymentIntegration extends Model
{
    protected $fillable = ['provider', 'environment', 'is_enabled', 'credentials', 'configuration', 'last_tested_at'];

    protected $hidden = ['credentials'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'credentials' => 'encrypted:array', 'configuration' => 'encrypted:array', 'last_tested_at' => 'datetime'];
    }

    public function accessToken(): string
    {
        return (string) ($this->credentials['access_token'] ?? '');
    }

    public function webhookSecret(): string
    {
        return (string) ($this->credentials['webhook_secret'] ?? '');
    }
}
