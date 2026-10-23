<?php

declare(strict_types=1);

namespace App\Actions\Catalog\Storefront;

use App\Domain\Catalog\Models\Category;
use App\Domain\Settings\Models\StoreSetting;
use App\Support\Storefront\Links;

final class GetStoreContextAction
{
    public function execute(): array
    {
        $settings = StoreSetting::query()->find(StoreSetting::MAIN_ID);

        return [
            'settings' => $settings,
            'storeName' => $settings?->store_name ?: config('app.name', 'Saba Equipamentos'),
            'whatsappUrl' => Links::whatsapp($settings?->whatsapp),
            'navigationCategories' => Category::visibleInStore()->ordered()->get(['id', 'name', 'slug']),
        ];
    }
}
