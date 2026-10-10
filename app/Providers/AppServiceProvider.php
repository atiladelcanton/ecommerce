<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Address\Contracts\PostalCodeLookup;
use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Infrastructure\Address\ViaCepPostalCodeLookup;
use App\Infrastructure\Payments\FakePaymentGateway;
use App\Infrastructure\Shipping\FakeShippingProvider;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PostalCodeLookup::class, fn (): PostalCodeLookup => match (config('address.provider')) {
            'viacep' => new ViaCepPostalCodeLookup,
            default => throw new InvalidArgumentException('Unsupported postal code lookup provider.'),
        });
        $this->app->bind(PaymentGateway::class, FakePaymentGateway::class);
        $this->app->bind(ShippingProvider::class, FakeShippingProvider::class);
    }

    public function boot(): void
    {
        // Application bootstrapping belongs here.
    }
}
