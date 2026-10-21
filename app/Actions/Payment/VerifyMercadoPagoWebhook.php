<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Domain\Payment\Models\PaymentIntegration;
use Illuminate\Http\Request;

final class VerifyMercadoPagoWebhook
{
    public function execute(Request $request): ?PaymentIntegration
    {
        $id = (string) $request->query('data.id', $request->query('data_id', ''));
        $signature = (string) $request->header('x-signature', '');
        $requestId = (string) $request->header('x-request-id', '');
        if (! preg_match('/^ORD[A-Za-z0-9_-]+$/', $id) || $requestId === '' || strlen($requestId) > 255 || strlen($signature) > 512) {
            return null;
        }
        $params = [];
        foreach (explode(',', $signature) as $chunk) {
            $pair = explode('=', trim($chunk), 2);
            if (count($pair) === 2) {
                $params[$pair[0]] = $pair[1];
            }
        }
        $ts = (string) ($params['ts'] ?? '');
        $v1 = (string) ($params['v1'] ?? '');
        if (! ctype_digit($ts) || strlen($ts) > 16 || ! preg_match('/^[a-fA-F0-9]{64}$/', $v1)) {
            return null;
        }
        $seconds = (int) $ts;
        $seconds = $seconds > 9999999999 ? intdiv($seconds, 1000) : $seconds;
        if (abs(time() - $seconds) > 600) {
            return null;
        }
        $manifest = 'id:'.strtolower($id).';request-id:'.$requestId.';ts:'.$ts.';';
        foreach (PaymentIntegration::query()->where('provider', 'mercado_pago')->get() as $integration) {
            $secret = $integration->webhookSecret();
            if ($secret !== '' && hash_equals(hash_hmac('sha256', $manifest, $secret), strtolower($v1))) {
                return $integration;
            }
        }
        return null;
    }
}
