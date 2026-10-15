<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Infrastructure\Shipping\FakeShippingProvider;

trait UsesDemoShipping
{
    protected function setUpUsesDemoShipping(): void
    {
        config(['shipping.default' => 'fake', 'shipping.providers.fake' => DemoShippingAdapter::class]);
        $this->app->bind(ShippingProvider::class, FakeShippingProvider::class);
        ShippingIntegration::forProvider('fake')->update(['is_enabled' => true]);
    }
}
