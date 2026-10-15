<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class TrustedProxyTest extends TestCase
{
    public function test_authentication_redirect_uses_https_behind_a_trusted_tunnel(): void
    {
        config()->set('trustedproxy.proxies', '*');

        $this->withServerVariables(['REMOTE_ADDR' => '172.18.0.1'])
            ->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('http://saba.ngrok-free.app/admin/shipping/melhor-envio/connect')
            ->assertRedirect('https://saba.ngrok-free.app/admin/login');
    }

    public function test_forwarded_https_is_ignored_without_a_trusted_proxy(): void
    {
        config()->set('trustedproxy.proxies', null);

        $this->withServerVariables(['REMOTE_ADDR' => '172.18.0.1'])
            ->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('http://localhost/admin/shipping/melhor-envio/connect')
            ->assertRedirect('http://localhost/admin/login');
    }
}
