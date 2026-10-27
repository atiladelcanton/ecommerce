<?php

declare(strict_types=1);

namespace Tests\Feature\Shipping;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\GetCartAction;
use App\Actions\Order\CreateInitialOrderAction;
use App\Actions\Order\GetCheckoutTokenAction;
use App\Actions\Order\TransitionOrderStatusAction;
use App\Actions\Shipping\CancelShipmentAction;
use App\Actions\Shipping\ConfigureShippingCarrierAction;
use App\Actions\Shipping\DownloadShipmentLabelAction;
use App\Actions\Shipping\GenerateShipmentLabelAction;
use App\Actions\Shipping\PrepareReplacementShipmentAction;
use App\Actions\Shipping\ProcessShippingWebhookAction;
use App\Actions\Shipping\QuoteShippingAction;
use App\Actions\Shipping\ReconcileShipmentAction;
use App\Actions\Shipping\SyncMelhorEnvioCatalogAction;
use App\Actions\Shipping\UpdateShippingIntegrationAction;
use App\Actions\Shipping\VerifyShipmentAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Customer\Models\Customer;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Settings\Models\StoreSetting;
use App\Domain\Shipping\Data\ApiMoney;
use App\Domain\Shipping\Data\ShippingItem;
use App\Domain\Shipping\Data\ShippingQuoteRequest;
use App\Domain\Shipping\Enums\ShipmentDocumentType;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Exceptions\ShippingProviderFailed;
use App\Domain\Shipping\Models\ShippingAgency;
use App\Domain\Shipping\Models\ShippingCarrier;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Filament\Pages\ShippingMethods;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Infrastructure\Shipping\MelhorEnvio\MelhorEnvioClient;
use App\Infrastructure\Shipping\MelhorEnvio\MelhorEnvioShippingProvider;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\ShippingFixtures;
use Tests\TestCase;

final class ShippingModuleTest extends TestCase
{
    use RefreshDatabase;
    use ShippingFixtures;

    public function test_registry_only_returns_enabled_implemented_providers(): void
    {
        ShippingIntegration::forProvider('superfrete')->update(['is_enabled' => true]);
        ShippingIntegration::forProvider('correios')->update(['is_enabled' => true]);
        ShippingIntegration::forProvider('local_pickup')->update(['is_enabled' => true]);
        $this->assertSame(['local_pickup'], array_map(fn ($provider) => $provider->key(), app(ShippingProviderRegistry::class)->enabled()));
        $this->assertFalse(app(ShippingProviderRegistry::class)->capabilities('local_pickup')->labels);
    }

    public function test_unimplemented_provider_cannot_be_enabled(): void
    {
        $this->expectException(ValidationException::class);
        app(UpdateShippingIntegrationAction::class)->execute('superfrete', ['is_enabled' => true]);
    }

    public function test_startup_and_checkout_work_without_shipping_configuration(): void
    {
        $this->get('/')->assertOk();
        $this->get('/checkout')->assertOk();
        $this->assertSame([], app(ShippingProviderRegistry::class)->enabled());
        Http::assertNothingSent();
    }

    public function test_no_fake_quotes_are_returned_without_configuration(): void
    {
        $this->store();
        app(AddCartItemAction::class)->execute(Product::factory()->create()->id);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Frete sob consulta');
        app(QuoteShippingAction::class)->execute(app(GetCartAction::class)->execute(), '13000000');
    }

    public function test_catalog_sync_preserves_choices_and_does_not_use_names_as_keys(): void
    {
        $this->carrier();
        Http::fake(['*/shipment/companies' => Http::response([['id' => 901, 'name' => 'Nome alterado']]), '*/shipment/services' => Http::response([['id' => 902, 'name' => 'Novo nome', 'company' => ['id' => 901]], ['id' => 903, 'name' => 'Novo serviço', 'company' => ['id' => 901]]])]);
        app(SyncMelhorEnvioCatalogAction::class)->execute();
        app(SyncMelhorEnvioCatalogAction::class)->execute();
        $this->assertDatabaseCount('shipping_carriers', 1);
        $this->assertDatabaseCount('shipping_services', 2);
        $this->assertDatabaseHas('shipping_services', ['external_id' => '902', 'is_enabled' => true, 'name' => 'Novo nome']);
        $this->assertDatabaseHas('shipping_services', ['external_id' => '903', 'is_enabled' => false, 'fulfillment_mode' => 'manual']);
    }

    public function test_agency_sync_filters_by_company_and_location_and_preserves_unrelated_agencies(): void
    {
        $carrier = $this->carrier();
        Http::fake(['*/shipment/agencies*' => Http::response([['id' => 'agency-1', 'company_id' => 901, 'name' => 'Unidade um', 'address' => ['street' => 'Rua A', 'city' => ['city' => 'São Paulo', 'state' => ['state_abbr' => 'SP']]]], ['id' => 'wrong', 'company_id' => 999, 'name' => 'Outra']])]);
        app(SyncMelhorEnvioCatalogAction::class)->agencies(['company' => '901', 'country' => 'BR', 'state' => 'SP', 'city' => 'São Paulo']);
        app(SyncMelhorEnvioCatalogAction::class)->agencies(['company' => '901', 'country' => 'BR', 'state' => 'SP', 'city' => 'Outra']);
        $this->assertDatabaseCount('shipping_agencies', 1);
        $this->assertDatabaseHas('shipping_agencies', ['shipping_carrier_id' => $carrier->id, 'external_id' => 'agency-1', 'city' => 'São Paulo', 'state' => 'SP']);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'company=901') && str_contains($request->url(), 'state=SP') && $request->hasHeader('User-Agent'));
    }

    public function test_services_cannot_be_enabled_when_integration_is_disabled(): void
    {
        $carrier = $this->carrier();
        $carrier->integration->update(['is_enabled' => false]);
        $this->expectException(ValidationException::class);
        app(ConfigureShippingCarrierAction::class)->execute($carrier, ['is_enabled' => true, 'services' => [['id' => $carrier->services->first()->id, 'is_enabled' => true, 'fulfillment_mode' => 'automatic']]]);
    }

    public function test_disabled_services_do_not_trigger_quote_requests(): void
    {
        $carrier = $this->carrier();
        $carrier->services()->update(['is_enabled' => false]);
        $options = app(MelhorEnvioShippingProvider::class)->quote(new ShippingQuoteRequest('13000000', [new ShippingItem(1, 1, '1', '10', '20', '30', 10000)], '01001000'));
        $this->assertSame([], $options);
        Http::assertNothingSent();
    }

    public function test_provider_failure_does_not_hide_pickup_and_does_not_cache_failure(): void
    {
        $this->carrier();
        $this->store();
        ShippingIntegration::forProvider('local_pickup')->update(['is_enabled' => true, 'configuration' => ['address' => 'SABA']]);
        app(AddCartItemAction::class)->execute(Product::factory()->create(['weight' => '1', 'height' => '10', 'width' => '20', 'length' => '30'])->id);
        Http::fake(['*/shipment/calculate' => Http::response([], 503)]);
        $cart = app(GetCartAction::class)->execute();
        $this->assertSame('local_pickup', app(QuoteShippingAction::class)->execute($cart, '13000000')[0]->provider);
        $this->assertSame(0, app(QuoteShippingAction::class)->execute($cart, '13000000')[0]->price);
        Http::assertSentCount(2);
    }

    public function test_products_without_dimensions_do_not_call_carrier_api_but_allow_pickup(): void
    {
        $this->carrier();
        $this->store();
        ShippingIntegration::forProvider('local_pickup')->update(['is_enabled' => true]);
        app(AddCartItemAction::class)->execute(Product::factory()->create(['weight' => null])->id);
        $options = app(QuoteShippingAction::class)->execute(app(GetCartAction::class)->execute(), '13000000');
        $this->assertCount(1, $options);
        $this->assertSame('pickup', $options[0]->fulfillmentMode);
        Http::assertNothingSent();
    }

    public function test_oauth_callback_keeps_exact_redirect_uri_and_never_configures_carriers(): void
    {
        $this->integration();
        $this->actingAs(User::factory()->create());
        config(['shipping.melhor_envio.callback_url' => 'https://shop.test/admin/shipping/melhor-envio/callback']);
        $response = $this->get(route('admin.shipping.melhor-envio.connect'));
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertArrayNotHasKey('services', $query);
        $this->assertArrayNotHasKey('agency', $query);
        config(['shipping.melhor_envio.callback_url' => 'https://changed.test/callback']);
        Http::fake(['*/oauth/token' => Http::response(['access_token' => 'new-access', 'refresh_token' => 'new-refresh', 'expires_in' => 3600])]);
        $this->get(route('admin.shipping.melhor-envio.callback', ['state' => $query['state'], 'code' => 'code-test']))->assertRedirect();
        Http::assertSent(fn ($request) => $request['redirect_uri'] === $query['redirect_uri'] && $request->hasHeader('User-Agent'));
        $this->assertDatabaseCount('shipping_carriers', 0);
    }

    public function test_oauth_invalid_state_does_not_exchange_tokens(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.shipping.melhor-envio.connect'));
        $this->get(route('admin.shipping.melhor-envio.callback', ['state' => 'bad', 'code' => 'code']))->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_refresh_token_is_used_on_unauthorized_and_credentials_remain_encrypted(): void
    {
        $integration = $this->integration();
        Http::fake(['*/shipment/services' => Http::sequence()->push([], 401)->push([]), '*/oauth/token' => Http::response(['access_token' => 'renewed-access', 'refresh_token' => 'renewed-refresh', 'expires_in' => 3600])]);
        app(MelhorEnvioClient::class)->get('/api/v2/me/shipment/services');
        $this->assertSame('renewed-access', $integration->fresh()->access_token);
        $stored = DB::table('shipping_integrations')->first();
        $this->assertStringNotContainsString('secret-test', $stored->credentials);
        $this->assertStringNotContainsString('renewed-access', $stored->access_token);
        $this->assertStringNotContainsString('renewed-refresh', $stored->refresh_token);
        $this->assertArrayNotHasKey('access_token', $integration->fresh()->toArray());
        Http::assertSent(fn ($request) => $request->url() === 'https://sandbox.melhorenvio.com.br/oauth/token' && $request['grant_type'] === 'refresh_token');
    }

    public function test_environment_change_clears_tokens(): void
    {
        $this->integration();
        $changed = app(UpdateShippingIntegrationAction::class)->execute('melhor_envio', ['environment' => 'production', 'is_enabled' => true]);
        $this->assertNull($changed->access_token);
        $this->assertNull($changed->refresh_token);
    }

    public function test_exact_money_serializer_does_not_use_float(): void
    {
        $this->assertSame('{"insurance_value":1299.90}', ApiMoney::json(['insurance_value' => new ApiMoney(129990)]));
        $this->assertSame(129990, ApiMoney::cents('1299.90'));
        $this->expectException(\InvalidArgumentException::class);
        ApiMoney::cents(1.25);
    }

    public function test_label_generation_persists_snapshot_and_repeated_click_never_purchases_twice(): void
    {
        $order = $this->order();
        $this->api();
        $shipment = $this->generate($order);
        $again = $this->generate($order);
        $this->assertSame($shipment->id, $again->id);
        $this->assertSame(OrderStatus::READY_TO_SHIP, $order->fresh()->status);
        $this->assertSame(ShipmentStatus::LABEL_GENERATED, $shipment->status);
        $this->assertSame('Transportadora de teste', $shipment->carrier_name);
        Http::assertSentCount(4);
        $this->assertDatabaseCount('shipments', 1);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/cart')
            && $request['from']['address'] === 'Rua da loja'
            && $request['products'][0]['name'] === 'Produto histórico'
            && ! isset($request['to']['state_register']));
    }

    public function test_generate_request_does_not_mark_label_ready_until_provider_confirms_generated(): void
    {
        $order = $this->order();
        $trackingCalls = 0;
        Http::fake([
            '*/api/v2/me/cart' => Http::response(['id' => 'external-1', 'price' => '48.90']),
            '*/api/v2/me/shipment/checkout' => Http::response(['success' => true]),
            '*/api/v2/me/shipment/generate' => Http::response(['success' => true]),
            '*/api/v2/me/shipment/tracking' => function () use (&$trackingCalls) {
                $trackingCalls++;

                return Http::response(['external-1' => [
                    'status' => $trackingCalls === 1 ? 'released' : 'generated',
                    'tracking' => null,
                    'tracking_url' => null,
                ]]);
            },
        ]);

        $first = $this->generate($order);
        $this->assertSame(ShipmentStatus::PURCHASED, $first->fresh()->status);
        $this->assertNull($first->fresh()->generated_at);
        $this->assertNotNull(($first->fresh()->metadata ?? [])['generation_requested_at'] ?? null);
        $this->assertSame(OrderStatus::PROCESSING, $order->fresh()->status);

        $second = $this->generate($order);
        $this->assertSame(ShipmentStatus::LABEL_GENERATED, $second->fresh()->status);
        $this->assertNotNull($second->fresh()->generated_at);
        $this->assertSame(OrderStatus::READY_TO_SHIP, $order->fresh()->status);

        $generateRequests = collect(Http::recorded())->filter(
            fn (array $entry): bool => str_ends_with($entry[0]->url(), '/api/v2/me/shipment/generate'),
        );
        $this->assertCount(1, $generateRequests);
    }

    public function test_content_declaration_generates_label_without_invoice_and_declares_order_items(): void
    {
        $order = $this->order();
        StoreSetting::query()->update(['state_register' => '123456789']);
        $this->api();

        $shipment = app(GenerateShipmentLabelAction::class)->execute(
            $order,
            documentType: ShipmentDocumentType::CONTENT_DECLARATION,
        );

        $fresh = $order->fresh();
        $this->assertSame(ShipmentStatus::LABEL_GENERATED, $shipment->status);
        $this->assertSame(ShipmentDocumentType::CONTENT_DECLARATION, $fresh->shipping_document_type);
        $this->assertNull($fresh->invoice_key);
        $this->assertNull($fresh->dce_key);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/v2/me/cart')
            && ! isset($request['options']['invoice'])
            && ! isset($request['options']['dce'])
            && ($request['from']['state_register'] ?? null) === ''
            && ($request['products'][0]['name'] ?? null) === 'Produto histórico'
            && ($request['products'][0]['quantity'] ?? null) === 1);
    }

    public function test_content_declaration_above_one_thousand_is_blocked_before_external_purchase(): void
    {
        $order = $this->order();
        $snapshot = $order->shipping_quote_snapshot;
        $snapshot['packages'][0]['insurance_cents'] = 129990;
        $snapshot['metadata']['products'][0]['unit_price_cents'] = 129990;
        $order->update([
            'subtotal_cents' => 129990,
            'total_cents' => 134880,
            'shipping_quote_snapshot' => $snapshot,
        ]);
        $order->items()->update([
            'regular_unit_price' => 129990,
            'unit_price' => 129990,
            'total' => 129990,
        ]);

        try {
            app(GenerateShipmentLabelAction::class)->execute(
                $order,
                documentType: ShipmentDocumentType::CONTENT_DECLARATION,
            );
            self::fail('Expected declaration limit validation');
        } catch (ValidationException $exception) {
            $message = (string) collect($exception->errors())->flatten()->first();
            $this->assertStringContainsString('R$ 1.000,00', $message);
            $this->assertStringContainsString('NF-e', $message);
            $this->assertStringNotContainsString('outro meio de entrega', $message);
        }

        Http::assertNothingSent();
    }

    public function test_blocking_shipping_validation_is_shown_as_persistent_modal_and_can_reopen_label_as_invoice(): void
    {
        $order = $this->order();
        $snapshot = $order->shipping_quote_snapshot;
        $snapshot['packages'][0]['insurance_cents'] = 129990;
        $snapshot['metadata']['products'][0]['unit_price_cents'] = 129990;
        $order->update([
            'subtotal_cents' => 129990,
            'total_cents' => 134880,
            'shipping_quote_snapshot' => $snapshot,
        ]);
        $order->items()->update([
            'regular_unit_price' => 129990,
            'unit_price' => 129990,
            'total' => 129990,
        ]);

        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();

        Livewire::test(ViewOrder::class, ['record' => $order->id])
            ->mountAction('generateLabel')
            ->fillForm(['shipping_document_type' => ShipmentDocumentType::CONTENT_DECLARATION->value])
            ->callMountedAction()
            ->assertSet('blockingOperationModalOpen', true)
            ->assertSet('blockingOperationPrimaryAction', 'generate_invoice')
            ->assertSee('Não é possível gerar esta etiqueta com declaração de conteúdo')
            ->assertSee('Gerar com NF-e')
            ->assertDontSee('outro meio de entrega compatível')
            ->call('continueBlockingOperation')
            ->assertSet('blockingOperationModalOpen', false)
            ->assertActionMounted('generateLabel')
            ->assertFormSet(['shipping_document_type' => ShipmentDocumentType::INVOICE->value]);

        Http::assertNothingSent();
    }

    public function test_content_declaration_can_send_external_dce_key_without_invoice(): void
    {
        $order = $this->order();
        $this->api();
        $dceKey = str_repeat('7', 44);

        app(GenerateShipmentLabelAction::class)->execute(
            $order,
            dceKey: $dceKey,
            documentType: ShipmentDocumentType::CONTENT_DECLARATION,
        );

        $fresh = $order->fresh();
        $this->assertSame($dceKey, $fresh->dce_key);
        $this->assertNull($fresh->invoice_key);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/v2/me/cart')
            && ! isset($request['options']['invoice'])
            && ($request['options']['dce']['key'] ?? null) === $dceKey);
    }

    public function test_invoice_mode_still_requires_a_real_nfe_key_before_external_call(): void
    {
        $order = $this->order();

        try {
            app(GenerateShipmentLabelAction::class)->execute(
                $order,
                documentType: ShipmentDocumentType::INVOICE,
            );
            self::fail('Expected missing NF-e');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('NF-e', (string) collect($exception->errors())->flatten()->first());
        }

        Http::assertNothingSent();
    }

    public function test_cart_validation_error_surfaces_melhor_envio_message_instead_of_generic_failure(): void
    {
        $order = $this->order();
        Http::fake([
            '*/api/v2/me/cart' => Http::response(['errors' => ['options.invoice.key' => ['A Nota fiscal informada é inválida.']]], 422),
        ]);

        try {
            $this->generate($order);
            self::fail('Expected cart validation failure');
        } catch (ValidationException $exception) {
            $message = (string) collect($exception->errors())->flatten()->first();
            $this->assertStringContainsString('NF-e', $message);
            $this->assertStringContainsString('Nota fiscal informada é inválida', $message);
        }

        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/shipment/checkout'));
    }

    public function test_optional_jadlog_style_agency_is_not_sent_to_cart_when_service_does_not_require_it(): void
    {
        $order = $this->order();
        $carrier = ShippingCarrier::query()->where('shipping_integration_id', $order->shipping_integration_id)->firstOrFail();
        $agency = ShippingAgency::query()->create([
            'shipping_integration_id' => $order->shipping_integration_id,
            'environment' => 'sandbox',
            'external_id' => '123',
            'name' => 'Unidade opcional',
            'company_external_id' => $carrier->external_id,
            'is_active' => true,
        ]);
        $agency->carriers()->attach($carrier->id);
        $snapshot = $order->shipping_quote_snapshot;
        $snapshot['metadata']['supports_agency'] = true;
        $snapshot['metadata']['agency_required'] = false;
        $snapshot['metadata']['agency_id'] = $agency->id;
        $order->update(['shipping_quote_snapshot' => $snapshot]);
        $this->api();

        app(GenerateShipmentLabelAction::class)->execute($order, $this->validInvoiceKey(), agencyId: $agency->id);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/v2/me/cart') && ! isset($request['agency']));
    }

    public function test_invalid_nfe_model_is_rejected_before_calling_provider(): void
    {
        $order = $this->order();

        try {
            app(GenerateShipmentLabelAction::class)->execute($order, str_repeat('1', 44));
            self::fail('Expected invalid NF-e');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('modelo 55', (string) collect($exception->errors())->flatten()->first());
        }

        Http::assertNothingSent();
    }

    public function test_generation_failure_after_purchase_resumes_without_new_cart_or_checkout(): void
    {
        $order = $this->order();
        $this->api(422);
        try {
            $this->generate($order);
            self::fail('Expected generation failure');
        } catch (ValidationException) {
        }
        $shipment = $order->shipments()->sole();
        $this->assertSame(ShipmentStatus::PURCHASED, $shipment->status);
        $this->generate($order);
        $this->assertSame(OrderStatus::READY_TO_SHIP, $order->fresh()->status);
        Http::assertSentCount(4);
        $this->assertCount(1, Http::recorded(fn ($request) => str_ends_with($request->url(), '/checkout')));
    }

    public function test_uncertain_purchase_is_not_retried(): void
    {
        $order = $this->order();
        Http::fake(['*/me/cart' => Http::response(['id' => 'external-1']), '*/shipment/checkout' => Http::response([], 503)]);
        for ($i = 0; $i < 2; $i++) {
            try {
                $this->generate($order);
                self::fail('Expected reconciliation requirement');
            } catch (ValidationException) {
            }
        }
        Http::assertSentCount(2);
        $this->assertSame('purchase_in_progress', $order->shipments()->sole()->operation_phase);
    }

    public function test_missing_sender_data_does_not_call_api(): void
    {
        $order = $this->order();
        StoreSetting::query()->update(['street' => null]);
        try {
            $this->generate($order);
            self::fail('Expected missing sender');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('Complete os dados da loja', $exception->getMessage());
        }
        Http::assertNothingSent();
        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_payment_must_be_approved_before_label_purchase(): void
    {
        $order = $this->order();
        $order->update(['payment_status' => PaymentStatus::PENDING]);
        try {
            $this->generate($order);
            self::fail('Expected pending payment');
        } catch (ValidationException) {
        }
        Http::assertNothingSent();
    }

    public function test_pickup_does_not_purchase_label(): void
    {
        $order = $this->order();
        $snapshot = $order->shipping_quote_snapshot;
        $snapshot['fulfillment_mode'] = 'pickup';
        $order->update(['shipping_quote_snapshot' => $snapshot]);
        try {
            $this->generate($order);
            self::fail('Expected pickup rejection');
        } catch (ValidationException) {
        }
        Http::assertNothingSent();
        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_multiple_packages_require_all_posted_and_all_delivered(): void
    {
        $order = $this->order(2);
        $this->api();
        $this->generate($order);
        $shipments = $order->shipments()->orderBy('id')->get();
        $this->assertCount(2, $shipments);
        $this->assertSame(4890, $shipments->sum('customer_price_cents'));
        $action = app(ProcessShippingWebhookAction::class);
        $action->execute('melhor_envio', ['event' => 'order.posted', 'data' => ['id' => $shipments[0]->external_id]], 'posted-1');
        $this->assertSame(OrderStatus::READY_TO_SHIP, $order->fresh()->status);
        $action->execute('melhor_envio', ['event' => 'order.posted', 'data' => ['id' => $shipments[1]->external_id]], 'posted-2');
        $this->assertSame(OrderStatus::SHIPPED, $order->fresh()->status);
        $action->execute('melhor_envio', ['event' => 'order.delivered', 'data' => ['id' => $shipments[0]->external_id]], 'delivered-1');
        $this->assertSame(OrderStatus::SHIPPED, $order->fresh()->status);
        $action->execute('melhor_envio', ['event' => 'order.delivered', 'data' => ['id' => $shipments[1]->external_id]], 'delivered-2');
        $this->assertSame(OrderStatus::DELIVERED, $order->fresh()->status);
    }

    public function test_posted_without_tracking_and_out_of_order_events_are_safe(): void
    {
        $order = $this->order();
        $this->api();
        $shipment = $this->generate($order);
        $action = app(ProcessShippingWebhookAction::class);
        $payload = ['event' => 'order.posted', 'data' => ['id' => $shipment->external_id, 'updated_at' => '2026-10-14T12:00:00Z']];
        $action->execute('melhor_envio', $payload, 'posted');
        $action->execute('melhor_envio', $payload, 'posted');
        $action->execute('melhor_envio', ['event' => 'order.generated', 'data' => ['id' => $shipment->external_id, 'updated_at' => '2026-10-14T11:00:00Z']], 'late');
        $this->assertSame(OrderStatus::SHIPPED, $order->fresh()->status);
        $this->assertNull($shipment->fresh()->tracking_code);
        $this->assertSame(ShipmentStatus::POSTED, $shipment->fresh()->status);
        $this->assertDatabaseCount('shipment_events', 2);
    }

    public function test_automatic_shipping_cannot_be_moved_by_ordinary_admin_transition(): void
    {
        $order = $this->order();
        $this->api();
        $this->generate($order);
        $this->expectException(ValidationException::class);
        app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::SHIPPED, carrier: 'Transportadora');
    }

    public function test_label_download_uses_actual_format_endpoint_without_storing_binary(): void
    {
        $order = $this->order();
        $this->api();
        $shipment = $this->generate($order);
        Http::fake(['*/me/imprimir/zpl/external-1' => Http::response('ZPL CONTENT')]);
        $file = app(DownloadShipmentLabelAction::class)->execute($shipment, 'zpl');
        $this->assertSame('ZPL CONTENT', $file->contents);
        $this->assertSame('zpl', $shipment->fresh()->label_format);
        $this->assertStringNotContainsString('ZPL CONTENT', json_encode($shipment->fresh()->toArray()));
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/imprimir/zpl/external-1') && $request->hasHeader('Authorization'));
    }

    public function test_admin_open_label_uses_official_public_print_link(): void
    {
        $order = $this->order();
        $this->api();
        $shipment = $this->generate($order);

        Http::fake([
            '*/api/v2/me/shipment/print' => Http::response(['url' => 'https://melhorenvio.test/print/label-1']),
        ]);

        $this->actingAs(User::factory()->create());

        $this->get(route('admin.shipping.labels.open', ['shipment' => $shipment]))
            ->assertRedirect('https://melhorenvio.test/print/label-1');

        $this->assertSame('https://melhorenvio.test/print/label-1', $shipment->fresh()->label_url);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/v2/me/shipment/print'));
    }

    public function test_open_label_falls_back_to_official_preview_when_print_link_is_temporarily_unavailable(): void
    {
        $order = $this->order();
        $this->api();
        $shipment = $this->generate($order);

        Http::fake([
            '*/api/v2/me/shipment/tracking' => Http::response(['external-1' => ['status' => 'generated']]),
            '*/api/v2/me/shipment/print' => Http::response(['message' => 'Impressão ainda indisponível'], 400),
            '*/api/v2/me/shipment/preview' => Http::response(['url' => 'https://sandbox.melhorenvio.com.br/preview/label-1']),
        ]);

        $this->actingAs(User::factory()->create());

        $this->get(route('admin.shipping.labels.open', ['shipment' => $shipment]))
            ->assertRedirect('https://sandbox.melhorenvio.com.br/preview/label-1');

        $this->assertSame('https://sandbox.melhorenvio.com.br/preview/label-1', $shipment->fresh()->label_url);
        $this->assertSame('preview', ($shipment->fresh()->metadata ?? [])['label_link_source'] ?? null);
    }

    public function test_direct_label_file_falls_back_to_official_print_when_provider_file_is_unavailable(): void
    {
        $order = $this->order();
        $this->api();
        $shipment = $this->generate($order);

        Http::fake([
            '*/api/v2/me/imprimir/pdf/*' => Http::response(['message' => 'Arquivo indisponível neste serviço'], 404),
            '*/api/v2/me/shipment/print' => Http::response(['url' => 'https://melhorenvio.test/print/fallback']),
        ]);

        $this->actingAs(User::factory()->create());

        $this->get(route('admin.shipping.labels.download', ['shipment' => $shipment, 'format' => 'pdf']))
            ->assertRedirect('https://melhorenvio.test/print/fallback');
    }

    public function test_admin_shipping_page_requires_admin_guard_and_does_not_render_secrets(): void
    {
        $this->integration();
        $this->get('/admin/shipping-methods')->assertRedirect();
        $this->actingAs(User::factory()->create());
        $this->get('/admin/shipping-methods')->assertOk()->assertSee('Melhor Envio')->assertDontSee('Transportadoras e serviços')->assertDontSee('secret-test')->assertDontSee('access-test')->assertDontSee('refresh-test');
    }

    public function test_configuration_and_carrier_actions_render_with_filament_5(): void
    {
        $carrier = $this->carrier();
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
        Livewire::test(ShippingMethods::class)->mountAction('configureMelhorEnvio')->assertActionMounted('configureMelhorEnvio')->assertMountedActionModalSee('Credenciais da aplicação')->assertMountedActionModalDontSee('secret-test');
        $order = $this->orderForUi($carrier);
        Livewire::test(ViewOrder::class, ['record' => $order->id])->assertSee('Gerar etiqueta');
    }

    public function test_quote_snapshot_is_saved_at_checkout_and_uses_only_local_service_ids(): void
    {
        $this->carrier();
        $this->store();
        $product = Product::factory()->create(['price' => 129990, 'weight' => '1.250', 'height' => '10', 'width' => '20', 'length' => '30']);
        app(AddCartItemAction::class)->execute($product->id);
        Http::fake(['*/shipment/calculate' => Http::response([['id' => 902, 'name' => 'API display name', 'custom_price' => '48.90', 'delivery_time' => 8, 'delivery_range' => ['min' => 6, 'max' => 8], 'company' => ['id' => 901, 'name' => 'API carrier name'], 'packages' => [['price' => '48.90', 'insurance_value' => '1299.90', 'dimensions' => ['height' => 10, 'width' => 20, 'length' => 30], 'weight' => '1.25', 'products' => [['id' => (string) $product->id, 'quantity' => 1]]]]]])]);
        $order = app(CreateInitialOrderAction::class)->execute(['name' => 'Cliente', 'email' => 'cliente@test.com', 'phone' => '11999999999', 'document' => '52998224725', 'postal_code' => '13000000', 'street' => 'Rua', 'number' => '1', 'neighborhood' => 'Centro', 'city' => 'Campinas', 'state' => 'SP', 'shipping_option' => 'melhor_envio:902'], app(GetCheckoutTokenAction::class)->execute());
        $this->assertSame(4890, $order->shipping_cents);
        $this->assertEquals(['min' => 6, 'max' => 8], $order->shipping_quote_snapshot['estimated_range']);
        $this->assertSame('Transportadora de teste', $order->shipping_quote_snapshot['carrier_name']);
        $this->assertSame(129990, $order->shipping_quote_snapshot['packages'][0]['insurance_cents']);
        $this->assertNotNull($order->shipping_integration_id);
        Http::assertSent(fn ($request) => $request['services'] === '902' && str_contains($request->body(), '"insurance_value":1299.90'));
    }

    public function test_disabled_configuration_invalidates_cached_quotes(): void
    {
        $carrier = $this->carrier();
        $this->store();
        app(AddCartItemAction::class)->execute(Product::factory()->create(['weight' => '1', 'height' => '10', 'width' => '20', 'length' => '30'])->id);
        Http::fake(['*/shipment/calculate' => Http::response([['id' => 902, 'price' => '10.00', 'company' => ['id' => 901], 'packages' => [['price' => '10.00', 'dimensions' => ['height' => 10, 'width' => 20, 'length' => 30], 'weight' => '1']]]])]);
        $cart = app(GetCartAction::class)->execute();
        $this->assertCount(1, app(QuoteShippingAction::class)->execute($cart, '13000000'));
        app(ConfigureShippingCarrierAction::class)->execute($carrier, ['is_enabled' => true, 'services' => [['id' => $carrier->services->first()->id, 'is_enabled' => false, 'fulfillment_mode' => 'automatic']]]);
        $this->expectException(ValidationException::class);
        app(QuoteShippingAction::class)->execute($cart, '13000000');
    }

    public function test_connection_test_and_sync_work_while_integration_is_disabled(): void
    {
        $integration = $this->integration();
        $integration->update(['is_enabled' => false]);
        Http::fake(['*/shipment/companies' => Http::response([['id' => 901, 'name' => 'Carrier']]), '*/shipment/services' => Http::response([])]);
        app(SyncMelhorEnvioCatalogAction::class)->execute();
        $this->assertDatabaseCount('shipping_carriers', 1);
        $this->assertSame([], app(ShippingProviderRegistry::class)->enabled());
    }

    public function test_cancellation_checks_cancellable_and_does_not_cancel_order(): void
    {
        $order = $this->order();
        $this->api();
        $shipment = $this->generate($order);
        Http::fake(['*/shipment/cancellable' => Http::sequence()->push([$shipment->external_id => ['cancellable' => false]])->push([$shipment->external_id => ['cancellable' => true]]), '*/shipment/cancel' => Http::response(['success' => true])]);
        try {
            app(CancelShipmentAction::class)->execute($shipment, 'Reemitir');
            self::fail('Expected cancellation refusal');
        } catch (ValidationException) {
        }
        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/cancel'));
        app(CancelShipmentAction::class)->execute($shipment, 'Reemitir');
        $this->assertSame(OrderStatus::READY_TO_SHIP, $order->fresh()->status);
        $this->assertSame(ShipmentStatus::CANCELLED, $shipment->fresh()->status);
    }

    public function test_cancelled_label_requires_explicit_replacement_and_repeated_preparation_is_idempotent(): void
    {
        $order = $this->order();
        $this->api();
        $shipment = $this->generate($order);
        $shipment->update(['status' => ShipmentStatus::CANCELLED, 'cancelled_at' => now()]);
        $user = User::factory()->create();
        $replacement = app(PrepareReplacementShipmentAction::class)->execute($shipment, $user->id);
        $again = app(PrepareReplacementShipmentAction::class)->execute($shipment, $user->id);
        $this->assertSame($replacement->id, $again->id);
        $this->assertSame(2, $replacement->attempt);
        $this->assertSame(OrderStatus::PROCESSING, $order->fresh()->status);
        $this->assertNull($replacement->external_id);
        $this->assertDatabaseCount('shipments', 2);
    }

    public function test_uncertain_cart_is_reconciled_by_verified_operation_tag_without_creating_another(): void
    {
        $order = $this->order();
        Http::fake(['*/me/cart' => Http::response([], 503)]);
        try {
            $this->generate($order);
            self::fail('Expected uncertain cart');
        } catch (ValidationException) {
        }
        $shipment = $order->shipments()->sole();
        Http::fake(['*/me/orders/existing-cart' => Http::response(['status' => 'pending', 'service_id' => 902, 'tags' => [['tag' => 'saba-operation:'.$shipment->operation_key]]]), '*/shipment/tracking' => Http::response(['existing-cart' => ['status' => 'pending']])]);
        $updated = app(ReconcileShipmentAction::class)->execute($shipment, 'existing-cart');
        $this->assertSame('existing-cart', $updated->external_id);
        $this->assertSame(ShipmentStatus::CART_CREATED, $updated->status);
        $this->assertNull($updated->operation_phase);
    }

    public function test_verify_shipment_finds_uncertain_cart_by_operation_tag_and_continues_without_second_cart(): void
    {
        $order = $this->order();

        Http::fake([
            '*/api/v2/me/cart' => Http::response([], 503),
        ]);

        try {
            $this->generate($order);
            self::fail('Expected uncertain cart');
        } catch (ValidationException) {
        }

        $shipment = $order->shipments()->sole();
        $tag = 'saba-operation:'.$shipment->operation_key;

        Http::fake([
            '*/api/v2/me/cart' => function () {
                self::fail('Verification must not create a second cart');
            },
            '*/api/v2/me/orders*' => Http::response([[
                'id' => 'recovered-cart',
                'status' => 'pending',
                'service_id' => 902,
                'tags' => [['tag' => $tag]],
            ]]),
            '*/api/v2/me/orders/recovered-cart' => Http::response([
                'id' => 'recovered-cart',
                'status' => 'pending',
                'service_id' => 902,
                'tags' => [['tag' => $tag]],
            ]),
            '*/api/v2/me/shipment/checkout' => Http::response(['success' => true]),
            '*/api/v2/me/shipment/generate' => Http::response(['success' => true]),
        ]);

        $verified = app(VerifyShipmentAction::class)->execute($shipment);

        $this->assertSame('recovered-cart', $verified->external_id);
        $this->assertSame(ShipmentStatus::LABEL_GENERATED, $verified->status);
        $this->assertNull($verified->operation_phase);
        $this->assertSame(OrderStatus::READY_TO_SHIP, $order->fresh()->status);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/v2/me/shipment/checkout'));
    }

    public function test_verify_shipment_does_not_retry_uncertain_purchase_while_provider_is_still_pending(): void
    {
        $order = $this->order();

        Http::fake([
            '*/api/v2/me/cart' => Http::response(['id' => 'external-pending']),
            '*/api/v2/me/shipment/checkout' => Http::response([], 503),
        ]);

        try {
            $this->generate($order);
            self::fail('Expected uncertain purchase');
        } catch (ValidationException) {
        }

        $shipment = $order->shipments()->sole();
        $tag = 'saba-operation:'.$shipment->operation_key;

        Http::fake([
            '*/api/v2/me/shipment/checkout' => function () {
                self::fail('Verification must not retry an unconfirmed purchase');
            },
            '*/api/v2/me/orders/external-pending' => Http::response([
                'id' => 'external-pending',
                'status' => 'pending',
                'service_id' => 902,
                'tags' => [['tag' => $tag]],
            ]),
        ]);

        try {
            app(VerifyShipmentAction::class)->execute($shipment);
            self::fail('Expected provider payment to remain pending');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('ainda não confirmou o pagamento', (string) collect($exception->errors())->flatten()->first());
        }

        $this->assertSame('purchase_in_progress', $shipment->fresh()->operation_phase);
    }

    public function test_reconciliation_rejects_another_operations_external_id(): void
    {
        $order = $this->order();
        $this->api(422);
        try {
            $this->generate($order);
        } catch (ValidationException) {
        }
        $shipment = $order->shipments()->sole();
        Http::fake(['*/me/orders/wrong-cart' => Http::response(['status' => 'released', 'service_id' => 902, 'tags' => [['tag' => 'saba-operation:other']]])]);
        $this->expectException(ValidationException::class);
        app(ReconcileShipmentAction::class)->execute($shipment, 'wrong-cart');
    }

    public function test_agency_from_another_carrier_is_rejected_before_external_call(): void
    {
        $order = $this->order();
        $agency = ShippingAgency::query()->create(['shipping_integration_id' => $order->shipping_integration_id, 'environment' => 'sandbox', 'external_id' => 'agency-test', 'name' => 'Outra', 'company_external_id' => 'other']);
        try {
            app(GenerateShipmentLabelAction::class)->execute($order, $this->validInvoiceKey(), agencyId: $agency->id);
            self::fail('Expected wrong agency');
        } catch (ValidationException) {
        }
        Http::assertNothingSent();
    }

    public function test_historical_shipment_does_not_use_credentials_from_another_environment(): void
    {
        $order = $this->order();
        $this->api();
        $shipment = $this->generate($order);
        app(UpdateShippingIntegrationAction::class)->execute('melhor_envio', ['environment' => 'production']);
        $this->expectException(ValidationException::class);
        app(DownloadShipmentLabelAction::class)->execute($shipment, 'pdf');
    }

    public function test_webhook_requires_valid_signature_and_customer_cannot_access_admin(): void
    {
        $this->integration();
        $this->postJson(route('webhooks.shipping.melhor-envio'), ['event' => 'order.posted', 'data' => ['id' => 'unknown']])->assertUnauthorized();
        $customer = Customer::factory()->create();
        $this->actingAs($customer, 'customer')->get('/admin/shipping-methods')->assertRedirect();
        $this->get(route('admin.shipping.melhor-envio.connect'))->assertRedirect();
    }

    public function test_partial_catalog_failure_does_not_disable_previous_services(): void
    {
        $carrier = $this->carrier();
        Http::fake(['*/shipment/companies' => Http::response([['id' => 901, 'name' => 'Carrier']]), '*/shipment/services' => Http::response([], 503)]);
        try {
            app(SyncMelhorEnvioCatalogAction::class)->execute();
            self::fail('Expected failure');
        } catch (ShippingProviderFailed) {
        }
        $this->assertTrue($carrier->services->first()->fresh()->is_available);
        $this->assertTrue($carrier->services->first()->fresh()->is_enabled);
    }

    public function test_operational_alerts_do_not_create_commercial_order_statuses(): void
    {
        $order = $this->order();
        $this->api();
        $shipment = $this->generate($order);
        $action = app(ProcessShippingWebhookAction::class);
        $action->execute('melhor_envio', ['event' => 'order.posted', 'data' => ['id' => $shipment->external_id]], 'post');
        $action->execute('melhor_envio', ['event' => 'order.paused', 'data' => ['id' => $shipment->external_id]], 'pause');
        $this->assertSame(ShipmentStatus::PAUSED, $shipment->fresh()->status);
        $this->assertSame(OrderStatus::SHIPPED, $order->fresh()->status);
    }

    public function test_legacy_order_uses_its_historical_items_and_packages_without_requoting(): void
    {
        $order = $this->order();
        $snapshot = $order->shipping_quote_snapshot;
        $oldPackages = $snapshot['packages'];
        unset($snapshot['integration_id'], $snapshot['environment'], $snapshot['fulfillment_mode'], $snapshot['packages']);
        $snapshot['metadata'] = ['packages' => $oldPackages];
        $order->update(['shipping_environment' => 'sandbox', 'shipping_quote_snapshot' => $snapshot]);
        $this->api();
        $shipment = $this->generate($order);
        $this->assertSame(ShipmentStatus::LABEL_GENERATED, $shipment->status);
        Http::assertNotSent(fn ($request) => str_ends_with($request->url(), '/calculate'));
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/cart') && str_contains($request->body(), '"unitary_value":100.00'));
    }

    private function orderForUi(ShippingCarrier $carrier): Order
    {
        $order = Order::query()->create(['number' => 'UI-ORDER', 'checkout_token' => (string) Str::uuid(), 'checkout_session_hash' => hash('sha256', 'x'), 'customer_name' => 'Cliente', 'customer_email' => 'cliente@test.com', 'customer_phone' => '11999999999', 'customer_document' => '52998224725', 'status' => OrderStatus::PROCESSING, 'payment_status' => PaymentStatus::APPROVED, 'subtotal_cents' => 10000, 'shipping_cents' => 0, 'discount_cents' => 0, 'total_cents' => 10000, 'shipping_option_id' => 'x', 'shipping_provider' => 'melhor_envio', 'shipping_name' => 'Teste', 'shipping_description' => 'Teste', 'shipping_quote_snapshot' => ['fulfillment_mode' => 'automatic']]);

        return $order;
    }
}
