<?php

declare(strict_types=1);

namespace App\Domain\AI\Models;

use Illuminate\Database\Eloquent\Model;

final class AiSetting extends Model
{
    public const MAIN_ID = 1;

    protected $attributes = [
        'id' => self::MAIN_ID,
        'provider' => 'groq',
        'model' => 'openai/gpt-oss-120b',
        'is_enabled' => false,
    ];

    protected $fillable = [
        'provider',
        'is_enabled',
        'model',
        'api_key',
        'store_instructions',
        'monthly_request_limit',
    ];

    protected $hidden = ['api_key'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'api_key' => 'encrypted',
            'monthly_request_limit' => 'integer',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(['id' => self::MAIN_ID], [
            'provider' => 'groq',
            'model' => 'openai/gpt-oss-120b',
            'is_enabled' => false,
        ]);
    }

    public function isConfigured(): bool
    {
        return $this->is_enabled && filled($this->api_key) && filled($this->provider) && filled($this->model);
    }
}
