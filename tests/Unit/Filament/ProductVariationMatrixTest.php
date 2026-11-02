<?php

declare(strict_types=1);

namespace Tests\Unit\Filament;

use App\Filament\Support\ProductVariationMatrix;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProductVariationMatrixTest extends TestCase
{
    public function test_removing_the_last_characteristic_generates_no_rows(): void
    {
        $existing = [['id' => 19, 'attributes' => ['Modelo' => 'Único'], 'is_active' => true]];
        self::assertSame([], ProductVariationMatrix::generate([], $existing));
        self::assertTrue(ProductVariationMatrix::axesMatchRows([], []));
        self::assertFalse(ProductVariationMatrix::axesMatchRows([], $existing));
    }

    public function test_generates_all_combinations_in_the_order_entered(): void
    {
        $rows = ProductVariationMatrix::generate([
            ['name' => 'Tamanho', 'values' => ['P', 'M']],
            ['name' => 'Cor', 'values' => ['Preto', 'Branco']],
        ], []);

        self::assertSame(['P / Preto', 'P / Branco', 'M / Preto', 'M / Branco'], array_column($rows, 'combination_display'));
        self::assertSame(['Tamanho' => 'P', 'Cor' => 'Branco'], $rows[1]['attributes']);
        self::assertTrue($rows[0]['is_active']);
        self::assertNull($rows[0]['price']);
        self::assertNull($rows[0]['stock']);
    }

    public function test_supports_any_product_characteristic_without_predefined_names(): void
    {
        $rows = ProductVariationMatrix::generate([
            ['name' => 'Voltagem', 'values' => ['110 V', '220 V']],
            ['name' => 'Capacidade', 'values' => ['3 L', '5 L']],
            ['name' => 'Material', 'values' => ['Aço inox']],
        ], []);

        self::assertCount(4, $rows);
        self::assertSame('110 V / 3 L / Aço inox', $rows[0]['combination_display']);
        self::assertSame(['Voltagem' => '220 V', 'Capacidade' => '5 L', 'Material' => 'Aço inox'], $rows[3]['attributes']);
        self::assertTrue(ProductVariationMatrix::axesMatchRows([
            ['name' => 'Voltagem', 'values' => ['110 V', '220 V']],
            ['name' => 'Capacidade', 'values' => ['3 L', '5 L']],
            ['name' => 'Material', 'values' => ['Aço inox']],
        ], $rows));
    }

    public function test_supports_single_characteristic_for_unrelated_segments(): void
    {
        foreach ([
            ['name' => 'Fragrância', 'values' => ['Lavanda', 'Cítrico']],
            ['name' => 'Comprimento', 'values' => ['1,5 m', '3 m']],
            ['name' => 'Quantidade no pacote', 'values' => ['6', '12']],
            ['name' => 'Acabamento', 'values' => ['Fosco', 'Brilhante']],
        ] as $axis) {
            $rows = ProductVariationMatrix::generate([$axis], []);
            self::assertCount(2, $rows);
            self::assertSame([$axis['name'] => $axis['values'][0]], $rows[0]['attributes']);
        }
    }

    public function test_retains_existing_ids_and_all_own_values_for_matching_combinations(): void
    {
        $current = [
            ['id' => 91, 'attributes' => ['Cor' => 'Preto', 'Tamanho' => 'M'], 'price' => '129,90',
                'promotional_price' => 9900, 'stock' => 3, 'sku' => 'PRE-M', 'weight' => '0.200',
                'height' => '2.00', 'width' => '20.00', 'length' => '30.00', 'is_active' => false],
        ];
        $rows = ProductVariationMatrix::generate([
            ['name' => 'Tamanho', 'values' => ['M', 'G']],
            ['name' => 'Cor', 'values' => ['Preto']],
        ], $current);

        self::assertSame(91, $rows[0]['id']);
        self::assertSame('129,90', $rows[0]['price']);
        self::assertSame(9900, $rows[0]['promotional_price']);
        self::assertSame(3, $rows[0]['stock']);
        self::assertSame('PRE-M', $rows[0]['sku']);
        self::assertSame('0.200', $rows[0]['weight']);
        self::assertFalse($rows[0]['is_active']);
        self::assertNull($rows[1]['id']);
    }

    public function test_legacy_rows_are_translated_into_editable_types(): void
    {
        $axes = ProductVariationMatrix::axesFromRows([
            ['attributes' => ['Cor' => 'Azul', 'Tamanho' => 'P']],
            ['attributes' => ['Cor' => 'Preto', 'Tamanho' => 'P']],
            ['attributes' => ['Cor' => 'Azul', 'Tamanho' => 'G']],
        ]);

        self::assertSame([
            ['name' => 'Cor', 'values' => ['Azul', 'Preto']],
            ['name' => 'Tamanho', 'values' => ['P', 'G']],
        ], $axes);
    }

    public function test_unsaved_tag_changes_are_detected_without_requiring_full_cartesian_grid(): void
    {
        $rows = [
            ['attributes' => ['Cor' => 'Preto', 'Tamanho' => 'P']],
            ['attributes' => ['Cor' => 'Branco', 'Tamanho' => 'G']],
        ];
        $original = ProductVariationMatrix::axesFromRows($rows);
        self::assertTrue(ProductVariationMatrix::axesMatchRows($original, $rows));
        $original[0]['values'][] = 'Azul';
        self::assertFalse(ProductVariationMatrix::axesMatchRows($original, $rows));
    }

    public function test_duplicate_types_and_values_are_rejected(): void
    {
        foreach ([
            [['name' => 'Cor', 'values' => ['Azul']], ['name' => 'cor', 'values' => ['Rosa']]],
            [['name' => 'Tamanho', 'values' => ['P', 'p']]],
        ] as $axes) {
            try {
                ProductVariationMatrix::generate($axes, []);
                self::fail('Expected duplicate rejection');
            } catch (InvalidArgumentException $exception) {
                self::assertStringContainsString('repetid', $exception->getMessage());
            }
        }
    }

    public function test_does_not_allow_excessive_number_of_combinations(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProductVariationMatrix::generate([
            ['name' => 'Modelo', 'values' => array_map('strval', range(1, 20))],
            ['name' => 'Cor', 'values' => array_map('strval', range(1, 10))],
        ], []);
    }
}
