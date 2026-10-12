<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Address\Contracts\PostalCodeLookup;
use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Infrastructure\Address\ViaCepPostalCodeLookup;
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
        $this->app->bind(PaymentGateway::class, function (): PaymentGateway {
            $driver = (string) config('payments.default', 'fake');
            $class = config('payments.gateways.'.$driver);
            if (! is_string($class) || ! is_a($class, PaymentGateway::class, true)) {
                throw new InvalidArgumentException('Unsupported payment gateway.');
            }

            return app($class);
        });
        $this->app->bind(ShippingProvider::class, function (): ShippingProvider {
            $driver = (string) config('shipping.default', 'fake');
            $class = config('shipping.providers.'.$driver);
            if (! is_string($class) || ! is_a($class, ShippingProvider::class, true)) {
                throw new InvalidArgumentException('Unsupported shipping provider.');
            }

            return app($class);
        });
    }

    public function boot(): void
    {
        // Application bootstrapping belongs here.
    }
}
