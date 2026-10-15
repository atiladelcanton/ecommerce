<?php

declare(strict_types=1);

namespace Tests\Feature\Shipping;

use App\Actions\Shipping\BuildShipmentRequestAction;
use App\Actions\Shipping\ConfigureShippingCarrierAction;
use App\Actions\Shipping\SyncMelhorEnvioCatalogAction;
use App\Domain\Shipping\Models\ShippingAgency;
use App\Domain\Shipping\Models\ShippingCarrier;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Filament\Pages\ShippingMethods;
use App\Livewire\Admin\Shipping\MelhorEnvioConfiguration;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\ShippingFixtures;
use Tests\TestCase;

final class ShippingConfigurationTest extends TestCase
{
    use RefreshDatabase;
    use ShippingFixtures;

    public function test_provider_card_opens_configuration_and_carrier_is_saved_inside_it(): void
    {
        $carrier = $this->carrier();
        $service = $carrier->services->sole();
        $this->admin();
        Livewire::test(ShippingMethods::class)->assertSee('SuperFrete')->assertDontSee($carrier->name)
            ->mountAction('configureMelhorEnvio')->assertActionMounted('configureMelhorEnvio')
            ->assertSeeLivewire(MelhorEnvioConfiguration::class);

        Livewire::test(MelhorEnvioConfiguration::class)->call('selectTab', 'carriers')
            ->call('editCarrier', $carrier->id)->assertSee('Compra automática de etiqueta')
            ->set('carrierData.'.$carrier->id.'.services', [])
            ->call('saveCarrier')->assertHasNoErrors();
        $this->assertFalse($service->fresh()->is_enabled);
        Http::assertNothingSent();
    }

    public function test_areas_save_independently_and_tab_changes_preserve_unsaved_fields(): void
    {
        $integration = $this->integration();
        $this->admin();
        $component = Livewire::test(MelhorEnvioConfiguration::class)
            ->set('connectionData.application_name', 'Rascunho da conexão')
            ->set('connectionData.client_id', '')
            ->call('selectTab', 'operation')->set('operationData.insurance_mode', 'required')
            ->set('operationData.label_format', 'zpl')->call('saveOperation')->assertHasNoErrors()
            ->call('selectTab', 'connection')->assertSet('connectionData.application_name', 'Rascunho da conexão')
            ->assertSet('connectionData.client_id', '');
        $this->assertSame('zpl', $integration->fresh()->configuration['label_format']);
        $this->assertSame('access-test', $integration->fresh()->access_token);
        $component->call('saveConnection')->assertHasErrors(['connectionData.client_id']);
    }

    public function test_default_operational_settings_are_ready_to_save_without_credentials(): void
    {
        $this->admin();
        Livewire::test(MelhorEnvioConfiguration::class)->call('selectTab', 'operation')
            ->assertSet('operationData.insurance_mode', 'always')->assertSet('operationData.label_format', 'pdf')
            ->call('saveOperation')->assertHasNoErrors()->assertSet('tab', 'operation');
        $this->assertDatabaseHas('shipping_integrations', ['provider' => 'melhor_envio', 'is_enabled' => false]);
        Http::assertNothingSent();
    }

    public function test_pickup_has_its_own_modal_and_can_be_enabled_after_saving_address(): void
    {
        $this->admin();
        Livewire::test(ShippingMethods::class)->mountAction('configurePickup')
            ->fillForm(['label' => 'Retirada na SABA', 'address' => 'Rua da loja, 100', 'instructions' => 'Agende a retirada.', 'ready_days' => 2])
            ->callMountedAction()->assertHasNoErrors()->call('toggleProvider', 'local_pickup');
        $pickup = ShippingIntegration::query()->where('provider', 'local_pickup')->sole();
        $this->assertTrue($pickup->is_enabled);
        $this->assertSame('Rua da loja, 100', $pickup->configuration['address']);
        Http::assertNothingSent();
    }

    public function test_saving_connection_keeps_existing_secret_and_tokens_and_saves_only_connection_area(): void
    {
        $integration = $this->integration();
        $this->admin();
        Livewire::test(MelhorEnvioConfiguration::class)->assertSet('connectionData.client_secret', '')
            ->set('connectionData.application_name', 'SABA atualizada')
            ->set('connectionData.technical_email', 'tecnico@saba.test')
            ->set('operationData.label_format', 'jpeg')->call('saveConnection')->assertHasNoErrors()
            ->assertSet('connectionData.client_secret', '')->assertDontSee('secret-test')->assertDontSee('access-test')->assertDontSee('refresh-test');
        $integration->refresh();
        $this->assertSame('SABA atualizada', $integration->configuration['application_name']);
        $this->assertNotSame('jpeg', $integration->configuration['label_format'] ?? null);
        $this->assertSame('secret-test', $integration->credentials['client_secret']);
        $this->assertSame('access-test', $integration->access_token);
    }

    public function test_sync_normalizes_numeric_company_and_location_and_maps_actual_shared_agency_shape(): void
    {
        $first = $this->carrier();
        $second = ShippingCarrier::query()->create(['shipping_integration_id' => $first->shipping_integration_id, 'environment' => 'sandbox', 'external_id' => '903', 'name' => 'Segunda transportadora', 'is_enabled' => true]);
        Http::fake(['*/shipment/agencies*' => Http::response([$this->sharedAgency()])]);
        $action = app(SyncMelhorEnvioCatalogAction::class);
        $this->assertSame(1, $action->agencies(['company' => 901, 'country' => ' br ', 'state' => ' sp ', 'city' => ' São Paulo ']));
        $agency = ShippingAgency::query()->sole();
        $originalId = $agency->id;
        $first->update(['default_shipping_agency_id' => $agency->id]);
        $this->assertSame(1, $action->agencies(['company' => '903']));
        $this->assertSame($originalId, $agency->fresh()->id);
        $this->assertSame($originalId, $first->fresh()->default_shipping_agency_id);
        $this->assertSame($first->id, $agency->fresh()->shipping_carrier_id);
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $agency->carriers()->pluck('shipping_carriers.id')->all());
        $this->assertDatabaseCount('shipping_agency_carrier', 2);
        $this->assertDatabaseHas('shipping_agencies', ['street' => 'Rua da unidade', 'city' => 'São Paulo', 'state' => 'SP']);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'company=901') && str_contains($request->url(), 'state=SP') && str_contains($request->url(), 'country=BR') && $request->hasHeader('User-Agent'));
        app(ConfigureShippingCarrierAction::class)->execute($second, ['is_enabled' => true, 'agency_id' => $agency->id, 'services' => []]);
        $this->assertSame($originalId, $second->fresh()->default_shipping_agency_id);
    }

    public function test_sync_from_inside_modal_does_not_validate_connection_and_updates_select_options(): void
    {
        $carrier = $this->carrier();
        $this->admin();
        Http::fake(['*/shipment/agencies*' => Http::response([$this->sharedAgency()])]);
        $component = Livewire::test(MelhorEnvioConfiguration::class)->call('editCarrier', $carrier->id)
            ->set('carrierData.'.$carrier->id.'.automatic', [])
            ->call('updateCarrierAgencies', $carrier->id)->assertSet('tab', 'agencies')
            ->set('connectionData.client_id', '')->set('agencyData.company', 901)->set('agencyData.state', 'sp')
            ->call('syncAgencies')->assertHasNoErrors()
            ->call('editCarrier', $carrier->id)->assertSet('carrierData.'.$carrier->id.'.automatic', []);
        $agency = ShippingAgency::query()->sole();
        $component->set('carrierData.'.$carrier->id.'.agency_id', $agency->id)->call('saveCarrier')->assertHasNoErrors();
        $this->assertSame($agency->id, $carrier->fresh()->default_shipping_agency_id);
    }

    public function test_validation_errors_are_inline_and_do_not_trigger_api_or_generic_failure(): void
    {
        $this->carrier();
        $this->admin();
        Livewire::test(MelhorEnvioConfiguration::class)->call('selectTab', 'agencies')
            ->set('agencyData.company', '901')->set('agencyData.state', '1!')->call('syncAgencies')
            ->assertHasErrors(['agencyData.state'])->assertSee('Informe uma UF com duas letras.');
        Http::assertNothingSent();
    }

    public function test_api_failure_is_sanitized_and_keeps_saved_carrier_choices(): void
    {
        $carrier = $this->carrier();
        $this->admin();
        Http::fake(['*/shipment/agencies*' => Http::response(['debug' => 'private-provider-details'], 503)]);
        Livewire::test(MelhorEnvioConfiguration::class)->call('selectTab', 'agencies')
            ->set('agencyData.company', '901')->call('syncAgencies')->assertHasNoErrors()
            ->assertNotified('Não foi possível atualizar as unidades do Melhor Envio. Verifique a conexão e tente novamente.')
            ->assertDontSee('private-provider-details');
        $this->assertTrue($carrier->fresh()->is_enabled);
    }

    public function test_integration_must_be_enabled_before_enabling_service_and_error_is_inline(): void
    {
        $carrier = $this->carrier();
        $carrier->integration->update(['is_enabled' => false]);
        $this->admin();
        Livewire::test(MelhorEnvioConfiguration::class)->call('editCarrier', $carrier->id)->call('saveCarrier')
            ->assertHasErrors(['carrierData.'.$carrier->id.'.is_enabled'])->assertSee('Ative a integração');
    }

    public function test_other_environment_cannot_be_used_as_company_filter(): void
    {
        $integration = $this->integration();
        ShippingCarrier::query()->create(['shipping_integration_id' => $integration->id, 'environment' => 'production', 'external_id' => '999', 'name' => 'Produção']);
        try {
            app(SyncMelhorEnvioCatalogAction::class)->agencies(['company' => 999]);
            self::fail('Expected environment validation');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('company', $exception->errors());
        }
        Http::assertNothingSent();
    }

    public function test_label_can_use_shared_agency_even_when_legacy_company_is_different(): void
    {
        $order = $this->order();
        Http::fake(['*/shipment/agencies*' => Http::response([$this->sharedAgency()])]);
        app(SyncMelhorEnvioCatalogAction::class)->agencies(['company' => '901']);
        $agency = ShippingAgency::query()->sole();
        $agency->update(['company_external_id' => '903']);
        $snapshot = $order->shipping_quote_snapshot;
        $request = app(BuildShipmentRequestAction::class)->execute($order, $snapshot, $snapshot['packages'], str_repeat('1', 44), null, $agency->id);
        $this->assertNotNull($request);
        Http::assertSentCount(1);
    }

    public function test_configuration_requires_admin_even_when_mounted_directly(): void
    {
        Livewire::test(MelhorEnvioConfiguration::class)->assertForbidden();
    }

    public function test_stale_environment_cannot_write_new_environment_configuration(): void
    {
        $integration = $this->integration();
        $this->admin();
        $component = Livewire::test(MelhorEnvioConfiguration::class);
        $integration->update(['environment' => 'production']);
        $component->call('saveOperation')->assertHasErrors(['shipping']);
        Http::assertNothingSent();
    }

    private function admin(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
    }

    private function sharedAgency(): array
    {
        return ['id' => 'shared-1', 'name' => 'Unidade compartilhada', 'companies' => [['id' => 901], ['id' => 903]], 'address' => ['postal_code' => '01001000', 'address' => 'Rua da unidade', 'number' => '10', 'district' => 'Centro', 'city' => ['city' => 'São Paulo', 'state' => ['state_abbr' => 'SP']]]];
    }
}
