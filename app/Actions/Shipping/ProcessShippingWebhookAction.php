<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Order\Models\Order;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Models\Shipment;
use App\Domain\Shipping\Models\ShipmentEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class ProcessShippingWebhookAction
{
    public function __construct(private readonly ProjectOrderShippingStatusAction $project) {}

    public function execute(string $provider, array $payload, string $eventKey): void
    {
        $eventKey = hash('sha256', $provider.'|'.$eventKey);
        $event = (string) ($payload['event'] ?? '');
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $candidate = Shipment::query()->where('provider', $provider)->where('external_id', (string) ($data['id'] ?? ''))->first();
        if (! $candidate) {
            return;
        }
        DB::transaction(function () use ($candidate, $payload, $eventKey, $event, $data): void {
            $order = Order::query()->lockForUpdate()->findOrFail($candidate->order_id);
            $shipment = Shipment::query()->lockForUpdate()->findOrFail($candidate->id);
            if (ShipmentEvent::query()->where('provider_event_id', $eventKey)->exists()) {
                return;
            }
            $status = $this->status($event);
            try {
                $occurred = Carbon::parse($data['updated_at'] ?? $data['delivered_at'] ?? $data['posted_at'] ?? $data['generated_at'] ?? $data['paid_at'] ?? now());
            } catch (\Throwable) {
                $occurred = now();
            }
            $last = $shipment->metadata['last_event_at'] ?? null;
            $stale = $last && $occurred->lt(Carbon::parse($last));
            // O rastreamento pode responder "released" mesmo quando a
            // geração já foi confirmada. Nunca regredir etapas logísticas.
            $progress = [
                ShipmentStatus::PURCHASED->value => 1,
                ShipmentStatus::LABEL_GENERATED->value => 2,
                ShipmentStatus::POSTED->value => 3,
                ShipmentStatus::DELIVERED->value => 4,
            ];
            if ($status && isset($progress[$status->value], $progress[$shipment->status->value])
                && $progress[$status->value] < $progress[$shipment->status->value]) {
                $stale = true;
            }
            // Terminal facts cannot be undone by delayed/generated/unknown events.
            if (! $stale && $status && ! $shipment->delivered_at && $shipment->status !== ShipmentStatus::CANCELLED) {
                if (! $shipment->posted_at || in_array($status, [ShipmentStatus::DELIVERED, ShipmentStatus::UNDELIVERED, ShipmentStatus::PAUSED, ShipmentStatus::SUSPENDED], true)) {
                    $shipment->status = $status;
                }
                if ($status === ShipmentStatus::LABEL_GENERATED) {
                    $shipment->generated_at ??= $occurred;
                }
                if ($status === ShipmentStatus::POSTED) {
                    $shipment->posted_at ??= $occurred;
                }
                if ($status === ShipmentStatus::DELIVERED) {
                    $shipment->posted_at ??= $occurred;
                    $shipment->delivered_at ??= $occurred;
                }
                if ($status === ShipmentStatus::CANCELLED) {
                    $shipment->cancelled_at ??= $occurred;
                }
                if (in_array($status, [ShipmentStatus::PURCHASED, ShipmentStatus::LABEL_GENERATED, ShipmentStatus::POSTED, ShipmentStatus::DELIVERED], true)) {
                    $shipment->operation_phase = null;
                }
                $shipment->metadata = array_merge($shipment->metadata ?? [], ['last_event_at' => $occurred->toIso8601String()]);
            }
            if (! $stale) {
                if (filled($data['tracking'] ?? null)) {
                    $shipment->tracking_code = substr((string) $data['tracking'], 0, 255);
                }
                $url = $data['tracking_url'] ?? null;
                if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['https', 'http'], true)) {
                    $shipment->tracking_url = $url;
                }
            }
            $shipment->save();
            if ($shipment->tracking_code || $shipment->tracking_url) {
                $order->update([
                    'tracking_code' => $shipment->tracking_code ?: $order->tracking_code,
                    'tracking_url' => $shipment->tracking_url ?: $order->tracking_url,
                    'carrier' => $shipment->carrier_name ?: $order->carrier,
                ]);
            }
            ShipmentEvent::query()->create([
                'shipment_id' => $shipment->id, 'provider_event_id' => $eventKey, 'event_type' => substr($event, 0, 80),
                'status' => $status?->value, 'payload' => $payload, 'occurred_at' => $occurred, 'processed_at' => now(),
            ]);
            $this->project->execute($order);
        }, 3);
    }

    private function status(string $event): ?ShipmentStatus
    {
        return match ($event) {
            'order.released' => ShipmentStatus::PURCHASED, 'order.generated' => ShipmentStatus::LABEL_GENERATED,
            'order.posted', 'order.received' => ShipmentStatus::POSTED, 'order.delivered' => ShipmentStatus::DELIVERED,
            'order.cancelled', 'order.canceled' => ShipmentStatus::CANCELLED, 'order.paused' => ShipmentStatus::PAUSED,
            'order.suspended' => ShipmentStatus::SUSPENDED, 'order.undelivered' => ShipmentStatus::UNDELIVERED, default => null,
        };
    }
}
