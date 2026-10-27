<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

final class InventoryReservation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'expires_at' => 'immutable_datetime', 'consumed_at' => 'immutable_datetime', 'released_at' => 'immutable_datetime', 'restored_at' => 'immutable_datetime'];
    }
}
