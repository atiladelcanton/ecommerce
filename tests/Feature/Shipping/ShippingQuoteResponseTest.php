<?php

declare(strict_types=1);

namespace Tests\Feature\Shipping;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\GetCartAction;
use App\Actions\Catalog\SyncProductVariantsAction;
use App\Actions\Shipping\QuoteShippingAction;
use App\Domain\Catalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ShippingFixtures;
use Tests\TestCase;

final class ShippingQuoteResponseTest extends TestCase
{
    use RefreshDatabase;
    use ShippingFixtures;

    public static function shapes(): array
    {
        return [['object'], ['list'], ['data_object'], ['data_list']];
    }

    #[DataProvider('shapes')]
    public function test_official_quote_formats_reach_checkout_as_integer_cents(string $shape): void
    {
        $this->carrier();
        $this->store();
        $product = Product::factory()->create(['weight' => '1', 'height' => '40', 'width' => '30', 'length' => '50']);
        app(AddCartItemAction::class)->execute($product->id);
        $row = $this->row();
        $response = match ($shape) {
            'object' => $row, 'list' => [$row], 'data_object' => ['data' => $row], default => ['data' => [$row]]
        };
        Http::fake(['*/shipment/calculate' => Http::response($response)]);
        $options = app(QuoteShippingAction::class)->execute(app(GetCartAction::class)->execute(), '13077001');
        $this->assertCount(1, $options);
        $this->assertSame(3048, $options[0]->priceCents);
        $this->assertSame('1.0', $options[0]->packages[0]['weight']);
    }

    public function test_one_failing_service_does_not_discard_valid_service_or_cache_partial_failure(): void
    {
        $carrier = $this->carrier();
        $carrier->services()->create(['external_id' => '903', 'name' => 'Outro', 'is_enabled' => true]);
        $this->store();
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        Http::fake(['*/shipment/calculate' => fn ($request) => $request['services'] === '903' ? Http::response([], 503) : Http::response($this->row())]);
        $cart = app(GetCartAction::class)->execute();
        $this->assertCount(1, app(QuoteShippingAction::class)->execute($cart, '13077001'));
        $this->assertCount(1, app(QuoteShippingAction::class)->execute($cart, '13077001'));
        Http::assertSentCount(4);
    }

    public function test_shipping_uses_selected_variation_package_and_price_for_insurance(): void
    {
        $this->carrier();
        $this->store();
        $product = Product::factory()->create(['price' => 10000]);
        app(SyncProductVariantsAction::class)->execute($product, [['attributes' => ['Embalagem' => '900 ml'], 'price' => 5000, 'is_active' => true, 'weight' => '1.2', 'height' => '10', 'width' => '20', 'length' => '30']]);
        app(AddCartItemAction::class)->execute($product->id, $product->variants()->sole()->id);
        Http::fake(['*/shipment/calculate' => Http::response($this->row())]);
        app(QuoteShippingAction::class)->execute(app(GetCartAction::class)->execute(), '13077001');
        Http::assertSent(fn ($request) => $request['products'][0]['weight'] === 1.2 && $request['products'][0]['height'] === 10 && str_contains($request->body(), '"insurance_value":50.00'));
    }

    private function row(): array
    {
        return ['id' => 902, 'name' => 'Expresso', 'price' => '30.48', 'custom_price' => '30.48', 'company' => ['id' => 901], 'delivery_range' => ['min' => 2, 'max' => 3], 'packages' => [['dimensions' => ['height' => 40, 'width' => 30, 'length' => 50], 'weight' => '1.0', 'insurance_value' => '120.00', 'products' => [['id' => '1', 'quantity' => 1]]]]];
    }
}
