<?php

declare(strict_types=1);

namespace App\Domain\AI\Models;

use App\Domain\Catalog\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AiUsageLog extends Model
{
    protected $fillable = [
        'provider', 'model', 'action', 'product_id', 'user_id',
        'input_tokens', 'output_tokens', 'total_tokens', 'duration_ms',
        'successful', 'error_code',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'total_tokens' => 'integer',
            'duration_ms' => 'integer',
            'successful' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
