<?php

use App\Actions\Catalog\ManageOrderInventoryAction;
use App\Actions\Coupon\ExpireUnusedCouponReservationsAction;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('saba:about', function (): void {
    $this->info('Saba Equipamentos e-commerce starter');
})->purpose('Display project identification');

Artisan::command('saba:expire-stock-reservations', function (): void {
    $count = app(ManageOrderInventoryAction::class)->expire();
    $this->info($count.' reserva(s) expirada(s).');
})->purpose('Liberar reservas de estoque vencidas');

Schedule::command('saba:expire-stock-reservations')->everyMinute()->withoutOverlapping();

Artisan::command('saba:expire-coupon-reservations', function (): void {
    $count = app(ExpireUnusedCouponReservationsAction::class)->execute();
    $this->info($count.' reserva(s) de cupom liberada(s).');
})->purpose('Liberar reservas de cupons abandonadas e sem cobrança pendente');

Schedule::command('saba:expire-coupon-reservations')->everyFiveMinutes()->withoutOverlapping();


// Usar somente no terminal do servidor por quem administra a instalação.
// Permite recuperar acesso se a primeira conta for criada depois da migration.
Artisan::command('saba:grant-admin {email}', function (string $email): void {
    $group = \App\Domain\Access\Models\AdminGroup::query()->where('slug', 'administrador')->firstOrFail();
    $user = \App\Models\User::query()->where('email', $email)->firstOrFail();
    $user->update(['admin_group_id' => $group->id]);
    $this->info('Acesso de Administrador concedido a '.$user->email.'.');
})->purpose('Atribuir acesso de administrador a uma conta existente via terminal');
