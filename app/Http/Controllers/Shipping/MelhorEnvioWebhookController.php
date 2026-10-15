<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shipping;

use App\Actions\Shipping\ProcessShippingWebhookAction;
use App\Domain\Shipping\Models\ShippingIntegration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class MelhorEnvioWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessShippingWebhookAction $action): JsonResponse
    {
        $integration = ShippingIntegration::query()->where('provider', 'melhor_envio')->first();
        $secret = (string) (($integration?->credentials ?? [])['client_secret'] ?? '');
        if ($secret === '') {
            abort(503, 'Melhor Envio não configurado.');
        }

        $raw = $request->getContent();
        $expected = base64_encode(hash_hmac('sha256', $raw, $secret, true));
        $provided = trim((string) $request->header('X-ME-Signature', ''));
        abort_unless($provided !== '' && hash_equals($expected, $provided), 401, 'Assinatura inválida.');

        $payload = $request->json()->all();
        $payload = is_array($payload) ? $payload : [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $eventKey = hash('sha256', implode('|', [
            (string) ($payload['event'] ?? ''),
            (string) ($data['id'] ?? ''),
            (string) ($data['status'] ?? ''),
            (string) ($data['posted_at'] ?? $data['delivered_at'] ?? $data['canceled_at'] ?? $data['updated_at'] ?? $data['created_at'] ?? ''),
        ]));
        $action->execute('melhor_envio', $payload, $eventKey);

        return response()->json(['ok' => true]);
    }
}
