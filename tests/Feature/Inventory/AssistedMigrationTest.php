<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Domain\Catalog\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class AssistedMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_combination_backfill_round_trip_preserves_color_ids_prices_and_stock(): void
    {
        $product = Product::factory()->create(['price' => 12000]);
        $variant = $product->variants()->create(['name' => 'Cor', 'value' => 'Preta', 'stock' => 4]);
        $migration = require database_path('migrations/2026_10_15_000002_extend_product_variant_combinations.php');
        $migration->down();
        $this->assertFalse(Schema::hasColumn('product_variants', 'attributes'));
        $migration->up();
        $row = DB::table('product_variants')->where('id', $variant->id)->first();
        $this->assertSame(['Cor' => 'Preta'], json_decode($row->attributes, true));
        $this->assertNull($row->price);
        $this->assertSame(4, $row->stock);
        $this->assertSame(12000, $product->fresh()->price);
        $this->assertSame(hash('sha256', json_encode(['cor' => 'preta'], JSON_UNESCAPED_UNICODE)), $row->combination_key);
    }
}
