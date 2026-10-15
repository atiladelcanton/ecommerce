<?php

declare(strict_types=1);

namespace App\Domain\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id', 'label', 'postal_code', 'street', 'number', 'complement',
        'neighborhood', 'city', 'state', 'is_default',
    ];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function formattedLine(): string
    {
        return trim($this->street.', '.$this->number.($this->complement ? ' - '.$this->complement : ''));
    }
}
