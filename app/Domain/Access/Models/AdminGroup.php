<?php

declare(strict_types=1);

namespace App\Domain\Access\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AdminGroup extends Model
{
    protected $fillable = ['name', 'slug', 'permissions'];

    protected function casts(): array
    {
        return ['permissions' => 'array'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'admin_group_id');
    }
}
