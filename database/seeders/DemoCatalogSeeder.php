<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Catalog\CreateCategoryAction;
use App\Actions\Catalog\CreateProductAction;
use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        DB::transaction(function (): void {
            $this->removePreviousDemo();
            $catalog = [
                'Máquinas de Serigrafia' => [
                    'Mesa Corrida SABA 4 Berços',
                    'Mesa Corrida SABA 6 Berços',
                    'Mesa Corrida SABA 8 Berços',
                    'Carrossel Manual SABA 4 Cores / 4 Berços',
                    'Carrossel Manual SABA 6 Cores / 6 Berços',
                    'Carrossel Manual SABA 8 Cores / 8 Berços',
                ],
                'Berços' => [
                    'Berço Adulto para Serigrafia', 'Berço Infantil para Serigrafia',
                    'Berço para Manga', 'Berço Universal',
                ],
                'Secagem e Cura' => [
                    'Estufa de Secagem SABA Compact', 'Estufa de Secagem SABA Industrial',
                    'Estufa Esteira SABA', 'Curadora UV SABA Compact',
                    'Curadora UV SABA Industrial', 'Flash Dryer SABA',
                ],
                'Tinta Plastisol' => ['Tinta Plastisol 1 kg'],
                'Tinta Base Água' => ['Tinta Base Água 1 kg'],
                'Telas e Quadros' => [
                    'Tela Serigráfica 40x50 cm', 'Tela Serigráfica 50x60 cm',
                    'Tela Serigráfica 60x80 cm', 'Quadro de Alumínio para Serigrafia',
                ],
                'Rodos' => [
                    'Rodo Serigráfico 20 cm', 'Rodo Serigráfico 30 cm',
                    'Rodo Serigráfico 40 cm', 'Borracha para Rodo 70 Shore',
                ],
                'Acessórios' => [
                    'Suporte para Tela', 'Presilha para Quadro',
                    'Espátula para Tinta', 'Kit de Ajuste para Berço',
                ],
            ];

            foreach (array_keys($catalog) as $sortOrder => $name) {
                $category = Category::query()->where('slug', Str::slug($name))->first()
                    ?? app(CreateCategoryAction::class)->execute(['name' => $name, 'sort_order' => $sortOrder]);

                foreach ($catalog[$name] as $productName) {
                    $product = Product::query()->where('slug', Str::slug($productName))->first()
                        ?? app(CreateProductAction::class)->execute([
                            'category_id' => $category->id, 'name' => $productName,
                            'short_description' => 'Produto fictício para demonstração local.',
                            'availability' => ProductAvailability::ON_DEMAND,
                            'cost_price' => 80000, 'price' => 129990,
                            'promotional_price' => $name === 'Máquinas de Serigrafia' ? 119990 : null,
                            'is_featured' => $name === 'Máquinas de Serigrafia',
                            'weight' => '15.000', 'height' => '80.00', 'width' => '60.00', 'length' => '90.00',
                        ]);

                    if (in_array($productName, ['Tinta Plastisol 1 kg', 'Tinta Base Água 1 kg'], true)) {
                        foreach (['Branco', 'Preto', 'Vermelho', 'Azul', 'Amarelo', 'Verde'] as $order => $color) {
                            $product->variants()->firstOrCreate(
                                ['name' => 'Cor', 'value' => $color],
                                ['sort_order' => $order],
                            );
                        }
                    }
                }
            }
        });
    }

    private function removePreviousDemo(): void
    {
        // Only retire unchanged fixtures from the previous local seed.
        Product::query()->where('short_description', 'Produto fictício para demonstração local.')
            ->whereIn('slug', [
                'maquina-serigrafica-saba-4x4', 'secador-saba-compacto',
                'estufa-saba-6-gavetas', 'prensa-saba-manual',
                'kit-saba-de-rodos', 'suporte-saba-de-reposicao',
            ])->delete();

        Category::query()->whereIn('slug', ['secadores', 'estufas', 'prensas', 'pecas'])
            ->whereDoesntHave('products')->delete();
    }
}
