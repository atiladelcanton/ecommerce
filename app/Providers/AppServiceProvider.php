<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind domain contracts to infrastructure implementations here.
    }

    public function boot(): void
    {
        // Application bootstrapping belongs here.
    }
}
