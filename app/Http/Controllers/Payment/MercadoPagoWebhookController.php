<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payment;

use App\Actions\Payment\VerifyMercadoPagoWebhook;
use App\Domain\Payment\Models\PaymentEvent;
use App\Jobs\ProcessMercadoPagoWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class MercadoPagoWebhookController
{
    public function __invoke(Request $request, VerifyMercadoPagoWebhook $verify): Response
    {
        $integration = $verify->execute($request);
        if (! $integration) {
            return response('Assinatura inválida.', 401);
        }
        $payload = $request->json()->all();
        $id = (string) $request->query('data.id', $request->query('data_id', '')); 
        if (($payload['type'] ?? null) !== 'order' || ($payload['data']['id'] ?? null) !== $id
            || ! is_string($payload['id'] ?? null) && ! is_int($payload['id'] ?? null)
            || (isset($payload['live_mode']) && (bool) $payload['live_mode'] !== ($integration->environment === 'production'))) {
            return response('Notificação inconsistente.', 422);
        }
        $event = PaymentEvent::query()->firstOrCreate([
            'gateway' => 'mercado_pago', 'environment' => $integration->environment,
            'event_key' => (string) $payload['id'],
        ], ['external_id' => $id, 'action' => mb_substr((string) ($payload['action'] ?? ''), 0, 100)]);
        if (! $event->processed_at) {
            ProcessMercadoPagoWebhook::dispatch($event->id);
        }
        return response('OK', 200);
    }
}
