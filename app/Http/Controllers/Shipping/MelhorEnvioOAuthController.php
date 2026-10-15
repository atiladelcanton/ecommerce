<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shipping;

use App\Domain\Shipping\Exceptions\ShippingProviderFailed;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Filament\Pages\ShippingMethods;
use App\Infrastructure\Shipping\MelhorEnvio\MelhorEnvioClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

final class MelhorEnvioOAuthController extends Controller
{
    public function connect(Request $request, MelhorEnvioClient $client): RedirectResponse
    {
        abort_unless(ShippingMethods::canAccess(), 403);
        $integration = ShippingIntegration::forProvider('melhor_envio');
        $state = Str::random(48);
        $request->session()->put('melhor_envio_oauth_state', $state);
        $request->session()->put('melhor_envio_oauth_started_at', now()->timestamp);
        $redirectUri = $client->callbackUrl();
        $request->session()->put('melhor_envio_oauth_redirect_uri', $redirectUri);
        $request->session()->put('melhor_envio_oauth_environment', $integration->environment);
        $request->session()->put('melhor_envio_oauth_client_id_hash', hash('sha256', trim((string) (($integration->credentials ?? [])['client_id'] ?? ''))));

        try {
            return redirect()->away($client->authorizeUrl($integration, $redirectUri, $state));
        } catch (ShippingProviderFailed $exception) {
            return redirect(ShippingMethods::getUrl())->with('error', $exception->getMessage());
        }
    }

    public function callback(Request $request, MelhorEnvioClient $client): RedirectResponse
    {
        abort_unless(ShippingMethods::canAccess(), 403);
        abort_unless((int) $request->session()->pull('melhor_envio_oauth_started_at', 0) >= now()->subMinutes(10)->timestamp, 403, 'Autorização expirada.');
        $state = (string) $request->query('state', '');
        $expected = (string) $request->session()->pull('melhor_envio_oauth_state', '');
        abort_unless($expected !== '' && hash_equals($expected, $state), 403, 'Estado OAuth inválido.');

        $code = trim((string) $request->query('code', ''));
        if ($code === '') {
            return redirect(ShippingMethods::getUrl())->with('error', 'O Melhor Envio não retornou o código de autorização.');
        }

        try {
            $integration = ShippingIntegration::forProvider('melhor_envio');
            $redirectUri = trim((string) $request->session()->pull('melhor_envio_oauth_redirect_uri', ''));
            $startedEnvironment = (string) $request->session()->pull('melhor_envio_oauth_environment', '');
            $startedClientIdHash = (string) $request->session()->pull('melhor_envio_oauth_client_id_hash', '');

            if ($redirectUri === '') {
                throw new ShippingProviderFailed('A URL de callback usada no início do OAuth não foi encontrada na sessão. Inicie a conexão novamente pelo painel.');
            }

            $currentClientId = trim((string) (($integration->credentials ?? [])['client_id'] ?? ''));
            if ($startedEnvironment !== '' && $startedEnvironment !== $integration->environment) {
                throw new ShippingProviderFailed('O ambiente do Melhor Envio foi alterado durante a autorização. Salve e inicie a conexão novamente.');
            }
            if ($startedClientIdHash !== '' && ! hash_equals($startedClientIdHash, hash('sha256', $currentClientId))) {
                throw new ShippingProviderFailed('O Client ID foi alterado durante a autorização. Salve e inicie a conexão novamente.');
            }

            // OAuth exige que o redirect_uri da troca do token seja EXATAMENTE o mesmo
            // usado para obter o authorization code. Nunca o recalculamos aqui.
            $tokens = $client->exchangeAuthorizationCode($integration, $code, $redirectUri);
            $client->storeTokens($integration, $tokens);

            return redirect(ShippingMethods::getUrl())->with('success', 'Conta do Melhor Envio conectada com sucesso.');
        } catch (ShippingProviderFailed $exception) {
            return redirect(ShippingMethods::getUrl())->with('error', $exception->getMessage());
        }
    }
}
