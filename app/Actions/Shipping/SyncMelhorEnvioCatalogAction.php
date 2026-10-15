<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Models\ShippingAgency;
use App\Domain\Shipping\Models\ShippingCarrier;
use App\Domain\Shipping\Models\ShippingService;
use App\Infrastructure\Shipping\MelhorEnvio\MelhorEnvioClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SyncMelhorEnvioCatalogAction
{
    public function __construct(private readonly MelhorEnvioClient $client) {}

    public function execute(): void
    {
        $integration = $this->client->integration();
        $companies = $this->rows($this->client->get('/api/v2/me/shipment/companies'));
        $services = $this->rows($this->client->get('/api/v2/me/shipment/services'));
        DB::transaction(function () use ($integration, $companies, $services): void {
            $scope = ShippingCarrier::query()->where('shipping_integration_id', $integration->id)->where('environment', $integration->environment);
            $scope->update(['is_available' => false]);
            ShippingService::query()->whereIn('shipping_carrier_id', $scope->pluck('id'))->update(['is_available' => false]);
            foreach ($companies as $row) {
                if (! isset($row['id'], $row['name'])) {
                    continue;
                }
                ShippingCarrier::query()->updateOrCreate([
                    'shipping_integration_id' => $integration->id, 'environment' => $integration->environment, 'external_id' => (string) $row['id'],
                ], ['name' => $row['name'], 'logo_url' => $row['picture'] ?? null, 'metadata' => $row, 'is_available' => true, 'last_synced_at' => now()]);
            }
            foreach ($services as $row) {
                $carrier = (clone $scope)->where('external_id', (string) ($row['company']['id'] ?? $row['company_id'] ?? ''))->first();
                if (! $carrier || ! isset($row['id'], $row['name'])) {
                    continue;
                }
                ShippingService::query()->updateOrCreate([
                    'shipping_carrier_id' => $carrier->id, 'external_id' => (string) $row['id'],
                ], ['name' => $row['name'], 'metadata' => $row, 'is_available' => true, 'last_synced_at' => now()]);
            }
            $this->invalidate();
        });
    }

    public function agencies(array $filters): int
    {
        $integration = $this->client->integration();
        foreach (['company', 'country', 'state', 'city'] as $field) {
            if (is_string($filters[$field] ?? null) || ($field === 'company' && is_int($filters[$field] ?? null))) {
                $filters[$field] = trim((string) $filters[$field]);
                if (in_array($field, ['country', 'state'], true)) {
                    $filters[$field] = strtoupper($filters[$field]);
                }
            }
        }
        $filters = Validator::make($filters, [
            'company' => ['nullable', 'string', 'max:100'], 'country' => ['nullable', 'in:BR'],
            'state' => ['nullable', 'regex:/^[A-Z]{2}$/'], 'city' => ['nullable', 'string', 'max:255'],
        ], ['state.regex' => 'Informe uma UF com duas letras.', 'country.in' => 'Selecione Brasil como país.'])->validate();
        $filters = array_filter($filters, fn ($value): bool => $value !== null && $value !== '');
        $carriers = ShippingCarrier::query()->where('shipping_integration_id', $integration->id)->where('environment', $integration->environment)->get()->toBase()->keyBy('external_id');
        if (isset($filters['company']) && ! $carriers->has($filters['company'])) {
            throw ValidationException::withMessages(['company' => 'Selecione uma transportadora sincronizada neste ambiente.']);
        }
        $rows = $this->rows($this->client->get('/api/v2/me/shipment/agencies', $filters));

        return DB::transaction(function () use ($integration, $rows, $filters, $carriers): int {
            $count = 0;
            foreach ($rows as $row) {
                if (! isset($row['id'])) {
                    continue;
                }
                $companyIds = array_key_exists('companies', $row)
                    ? collect($row['companies'])->pluck('id')->filter(fn ($id) => is_string($id) || is_int($id))->map(fn ($id) => (string) $id)->all()
                    : [(string) ($row['company']['id'] ?? $row['company_id'] ?? $filters['company'] ?? '')];
                if (isset($filters['company']) && ! in_array($filters['company'], $companyIds, true)) {
                    continue;
                }
                $linkedCarriers = $carriers->only($companyIds);
                if ($linkedCarriers->isEmpty()) {
                    continue;
                }
                $address = is_array($row['address'] ?? null) ? $row['address'] : $row;
                $city = $address['city'] ?? null;
                $state = $address['state'] ?? (is_array($city) ? ($city['state'] ?? null) : null);
                $agency = ShippingAgency::query()->firstOrNew([
                    'shipping_integration_id' => $integration->id, 'environment' => $integration->environment, 'external_id' => (string) $row['id'],
                ]);
                // Preserve legacy references; the pivot is authoritative for shared agencies.
                if (! $agency->exists) {
                    $agency->shipping_carrier_id = $linkedCarriers->first()->id;
                    $agency->company_external_id = $linkedCarriers->first()->external_id;
                }
                $agency->fill([
                    'name' => $row['name'] ?? $row['company_name'] ?? 'Unidade '.$row['id'],
                    'postal_code' => $address['postal_code'] ?? null, 'street' => $address['street'] ?? $address['address'] ?? (is_string($row['address'] ?? null) ? $row['address'] : null),
                    'number' => $address['number'] ?? null, 'district' => $address['district'] ?? null,
                    'city' => is_array($city) ? ($city['city'] ?? $city['name'] ?? null) : $city,
                    'state' => is_array($state) ? ($state['state_abbr'] ?? $state['abbreviation'] ?? null) : $state,
                    'is_active' => (bool) ($row['is_active'] ?? true), 'metadata' => $row, 'last_synced_at' => now(),
                ])->save();
                $agency->carriers()->syncWithoutDetaching($linkedCarriers->pluck('id')->all());
                $count++;
            }
            $this->invalidate();

            return $count;
        });
    }

    private function rows(array $response): array
    {
        $rows = array_is_list($response) ? $response : ($response['data'] ?? null);
        if (! is_array($rows) || ! array_is_list($rows) || ($response['next_page_url'] ?? null) || (($response['last_page'] ?? $response['meta']['last_page'] ?? 1) > 1) || ($response['links']['next'] ?? null)) {
            throw ValidationException::withMessages(['shipping' => 'O catálogo retornou uma listagem incompleta. Refine os filtros e tente novamente.']);
        }

        return $rows;
    }

    private function invalidate(): void
    {
        Cache::forever('shipping-configuration-revision', (string) Str::uuid());
    }
}
