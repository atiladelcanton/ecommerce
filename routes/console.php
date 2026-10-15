<?php

use App\Actions\Catalog\ManageOrderInventoryAction;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('saba:about', function (): void {
    $this->info('SABA Máquinas e-commerce starter');
})->purpose('Display project identification');

Artisan::command('saba:expire-stock-reservations', function (): void {
    $count = app(ManageOrderInventoryAction::class)->expire();
    $this->info($count.' reserva(s) expirada(s).');
})->purpose('Liberar reservas de estoque vencidas');

Schedule::command('saba:expire-stock-reservations')->everyMinute()->withoutOverlapping();
