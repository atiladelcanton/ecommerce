<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Models\ShippingAgency;
use App\Domain\Shipping\Models\ShippingCarrier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ConfigureShippingCarrierAction
{
    public function execute(ShippingCarrier $carrier, array $input): void
    {
        $data = Validator::make($input, [
            'is_enabled' => ['required', 'boolean'], 'agency_id' => ['nullable', 'integer'],
            'services' => ['present', 'array'], 'services.*.id' => ['required', 'integer'],
            'services.*.is_enabled' => ['required', 'boolean'], 'services.*.fulfillment_mode' => ['required', 'in:automatic,manual'],
        ])->validate();
        DB::transaction(function () use ($carrier, $data): void {
            $carrier = ShippingCarrier::query()->with(['integration', 'services'])->lockForUpdate()->findOrFail($carrier->id);
            if ($carrier->environment !== $carrier->integration->environment) {
                throw ValidationException::withMessages(['shipping' => 'Esta transportadora pertence a outro ambiente.']);
            }
            $activatingService = collect($data['services'])->contains(fn ($row) => $row['is_enabled']);
            if (($data['is_enabled'] || $activatingService) && (! $carrier->integration->is_enabled || ! $carrier->is_available)) {
                throw ValidationException::withMessages(['is_enabled' => 'Ative a integração e sincronize a transportadora antes de habilitar serviços.']);
            }
            if ($activatingService && ! $data['is_enabled']) {
                throw ValidationException::withMessages(['services' => 'Ative a transportadora antes de habilitar seus serviços.']);
            }
            $agencyId = $data['agency_id'] ?? null;
            if ($agencyId && ! ShippingAgency::query()->whereKey($agencyId)->whereHas('carriers', fn ($query) => $query->whereKey($carrier->id))->where('shipping_integration_id', $carrier->shipping_integration_id)->where('environment', $carrier->environment)->where('is_active', true)->exists()) {
                throw ValidationException::withMessages(['agency_id' => 'Selecione uma unidade ativa desta transportadora.']);
            }
            foreach ($data['services'] as $row) {
                $service = $carrier->services->firstWhere('id', $row['id']);
                if (! $service || ($row['is_enabled'] && ! $service->is_available)) {
                    throw ValidationException::withMessages(['services' => 'Serviço indisponível nesta transportadora.']);
                }
                if ($row['fulfillment_mode'] === 'automatic' && ($service->metadata['supports_purchase'] ?? true) === false) {
                    throw ValidationException::withMessages(['services' => 'Este serviço permite somente operação manual.']);
                }
                $service->update(['is_enabled' => $row['is_enabled'], 'fulfillment_mode' => $row['fulfillment_mode']]);
            }
            $carrier->update(['is_enabled' => $data['is_enabled'], 'default_shipping_agency_id' => $agencyId]);
            Cache::forever('shipping-configuration-revision', (string) Str::uuid());
        });
    }
}
