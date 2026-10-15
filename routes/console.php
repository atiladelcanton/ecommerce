<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('saba:about', function (): void {
    $this->info('SABA Máquinas e-commerce starter');
})->purpose('Display project identification');

Artisan::command('saba:expire-stock-reservations', function (): void {
    $count = app(\App\Actions\Catalog\ManageOrderInventoryAction::class)->expire();
    $this->info($count.' reserva(s) expirada(s).');
})->purpose('Liberar reservas de estoque vencidas');

\Illuminate\Support\Facades\Schedule::command('saba:expire-stock-reservations')->everyMinute()->withoutOverlapping();
