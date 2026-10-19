<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Contracts\ShipmentLabelProvider;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Exceptions\ShippingProviderFailed;
use App\Domain\Shipping\Models\Shipment;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use Illuminate\Validation\ValidationException;

final class PrintShipmentLabelAction
{
    public function __construct(
        private readonly ShippingProviderRegistry $providers,
        private readonly RefreshShipmentTrackingAction $refresh,
    ) {}

    public function execute(Shipment $shipment, string $format = 'pdf'): string
    {
        app(AssertShipmentConnectionAction::class)->execute($shipment);
        $provider = $this->providers->get($shipment->provider);

        if (! $provider instanceof ShipmentLabelProvider || ! $shipment->external_id) {
            throw ValidationException::withMessages(['shipping' => 'Este envio não possui uma etiqueta vinculada ao provedor.']);
        }

        // Corrige estados locais antigos/otimistas e confirma o ciclo real no
        // provedor antes de tentar imprimir. Se a consulta falhar, ainda podemos
        // tentar a impressão quando o estado local já indicava uma etiqueta pronta.
        try {
            $shipment = $this->refresh->execute($shipment);
        } catch (ValidationException) {
            $shipment->refresh();
        }

        if (! in_array($shipment->status, [
            ShipmentStatus::LABEL_GENERATED,
            ShipmentStatus::POSTED,
            ShipmentStatus::DELIVERED,
            ShipmentStatus::PAUSED,
            ShipmentStatus::SUSPENDED,
            ShipmentStatus::UNDELIVERED,
        ], true)) {
            $message = $shipment->status === ShipmentStatus::PURCHASED
                ? 'O Melhor Envio recebeu a solicitação, mas a etiqueta ainda está sendo processada. Aguarde alguns instantes e use “Atualizar status da etiqueta”.'
                : 'Esta etiqueta ainda não está disponível para impressão.';

            throw ValidationException::withMessages(['shipping' => $message]);
        }

        try {
            $url = $provider->printLabel($shipment->external_id, $format);
            $source = 'print';
        } catch (ShippingProviderFailed $printException) {
            // Em Sandbox o link de impressão pode ficar indisponível por alguns
            // instantes mesmo com o envio comprado/gerado. A pré-visualização é
            // o fallback oficial; ela exige sessão ativa no Melhor Envio.
            try {
                $url = $provider->previewLabel($shipment->external_id);
                $source = 'preview';
            } catch (ShippingProviderFailed $previewException) {
                throw ValidationException::withMessages([
                    'shipping' => $previewException->getMessage() ?: $printException->getMessage(),
                ]);
            }
        }

        $metadata = $shipment->metadata ?? [];
        $metadata['label_link_source'] = $source;
        $shipment->forceFill([
            'label_url' => $url,
            'label_format' => $format,
            'metadata' => $metadata,
        ])->save();

        return $url;
    }
}
