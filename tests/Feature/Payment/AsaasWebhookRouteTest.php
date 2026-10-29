<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class AsaasWebhookRouteTest extends TestCase
{
    public function test_asaas_webhook_has_the_name_and_post_url_expected_by_the_payment_page(): void
    {
        $this->assertTrue(Route::has('webhooks.payments.asaas'));
        $this->assertSame(
            url('/webhooks/payments/asaas'),
            route('webhooks.payments.asaas'),
        );
        $this->assertContains(
            'POST',
            Route::getRoutes()->getByName('webhooks.payments.asaas')->methods(),
        );
    }

    public function test_asaas_webhook_refuses_unauthenticated_requests(): void
    {
        // The endpoint must remain public to Asaas, but not process events without its token.
        $this->postJson(route('webhooks.payments.asaas'), [
            'id' => 'evt-test-event',
            'event' => 'CHECKOUT_PAID',
        ])->assertUnauthorized();
    }
}
