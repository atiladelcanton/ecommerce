<?php

declare(strict_types=1);

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Model;

final class StoreSetting extends Model
{
    public const MAIN_ID = 1;

    protected $attributes = ['id' => self::MAIN_ID];

    protected $fillable = [
        'store_name',
        'legal_name', 'state_register', 'economic_activity_code',
        'cnpj',
        'phone',
        'whatsapp',
        'email',
        'zip_code',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'instagram_url',
        'facebook_url',
        'youtube_url',
        'seo_title',
        'seo_description',
        'shipping_origin_zip_code',
        'blog_enabled',
        'blog_show_on_home',
    ];

    protected function casts(): array
    {
        return ['blog_enabled' => 'boolean', 'blog_show_on_home' => 'boolean'];
    }

    protected $hidden = ['singleton'];
}
