<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payment;

use App\Domain\Payment\Models\PaymentEvent;
use App\Domain\Payment\Models\PaymentIntegration;
use App\Jobs\ProcessAsaasWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class AsaasWebhookController
{
    public function __invoke(Request $request): Response
    {
        $provided = (string) $request->header('asaas-access-token', '');
        if ($provided === '' || strlen($provided) > 4096) {
            return response('Unauthorized', 401);
        }
        $integration = null;
        foreach (PaymentIntegration::query()->where('provider', 'asaas')->get() as $candidate) {
            $secret = $candidate->webhookSecret();
            if (strlen($secret) >= 32 && hash_equals($secret, $provided)) {
                $integration = $candidate;
                break;
            }
        }
        if ($integration === null) {
            return response('Unauthorized', 401);
        }
        $json = $request->json()->all();
        $event = $json['event'] ?? null;
        if (! in_array($event, ['CHECKOUT_CREATED', 'CHECKOUT_PAID', 'CHECKOUT_CANCELED', 'CHECKOUT_EXPIRED'], true)) {
            // Forward-compatible: unrelated events must not hold up the Asaas queue.
            return response('OK', 200);
        }
        $eventId = $json['id'] ?? null;
        $checkout = $json['checkout'] ?? null;
        $checkoutId = is_array($checkout) ? ($checkout['id'] ?? null) : null;
        if (! is_string($eventId) || ! preg_match('/^[a-zA-Z0-9_&-]{5,191}$/', $eventId)
            || ! is_string($checkoutId) || ! preg_match('/^[a-zA-Z0-9_-]{12,100}$/', $checkoutId)
            || ! is_array($checkout) || count($checkout) > 100) {
            return response('Invalid event', 422);
        }
        // Persist only operational checkout fields, never payer identity or card data.
        $minimal = ['checkout' => array_intersect_key($checkout, array_flip(['id', 'status', 'externalReference', 'items']))];
        $row = PaymentEvent::query()->firstOrCreate([
            'gateway' => 'asaas', 'environment' => $integration->environment, 'event_key' => $eventId,
        ], [
            'external_id' => $checkoutId, 'action' => $event, 'payload' => $minimal,
        ]);
        if ($row->external_id !== $checkoutId || $row->action !== $event) {
            return response('Conflict', 409);
        }
        if (! $row->processed_at) {
            ProcessAsaasWebhook::dispatch($row->id);
        }
        return response('OK', 200);
    }
}
