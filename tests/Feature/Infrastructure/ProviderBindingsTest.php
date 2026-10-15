<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Infrastructure\Payments\FakePaymentGateway;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use Tests\TestCase;

final class ProviderBindingsTest extends TestCase
{
    public function test_default_payment_gateway_is_resolved_from_configuration(): void
    {
        $this->assertSame('fake', config('payments.default'));
        $this->assertInstanceOf(FakePaymentGateway::class, app(PaymentGateway::class));
    }

    public function test_shipping_uses_registry_without_a_default_fake(): void
    {
        $this->assertFalse($this->app->bound(ShippingProvider::class));
        $this->assertArrayNotHasKey('fake', config('shipping.providers'));
        $this->assertInstanceOf(ShippingProviderRegistry::class, app(ShippingProviderRegistry::class));
    }
}
