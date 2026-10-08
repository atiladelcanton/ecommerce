<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Catalog\CreateCategoryAction;
use App\Actions\Catalog\CreateProductAction;
use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        $catalog = [
            'Máquinas de Serigrafia' => 'Máquina Serigráfica SABA 4x4',
            'Secadores' => 'Secador SABA Compacto',
            'Estufas' => 'Estufa SABA 6 Gavetas',
            'Prensas' => 'Prensa SABA Manual',
            'Acessórios' => 'Kit SABA de Rodos',
            'Peças' => 'Suporte SABA de Reposição',
        ];
        foreach ($catalog as $name => $productName) {
            $category = Category::query()->where('slug', Str::slug($name))->first()
                ?? app(CreateCategoryAction::class)->execute(['name' => $name, 'sort_order' => array_search($name, array_keys($catalog), true)]);
            if (! Product::query()->where('slug', Str::slug($productName))->exists()) {
                app(CreateProductAction::class)->execute([
                    'category_id' => $category->id, 'name' => $productName,
                    'short_description' => 'Produto fictício para demonstração local.',
                    'availability' => ProductAvailability::ON_DEMAND,
                    'cost_price' => 80000, 'price' => 129990,
                    'promotional_price' => $name === 'Máquinas de Serigrafia' ? 119990 : null,
                    'is_featured' => $name === 'Máquinas de Serigrafia',
                    'weight' => '15.000', 'height' => '80.00', 'width' => '60.00', 'length' => '90.00',
                ]);
            }
        }
    }
}
