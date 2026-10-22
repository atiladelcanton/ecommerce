<?php

use App\Actions\Catalog\ManageOrderInventoryAction;
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

Artisan::command('saba:reconcile-payments {orderNumber?}', function (): void {
    $order = $this->argument('orderNumber');
    $count = app(\App\Actions\Payment\ReconcilePendingPaymentsAction::class)->execute(50, is_string($order) && $order !== '' ? $order : null);
    $this->info($count.' cobranças verificadas.');
})->purpose('Conciliar cobranças pendentes sem liberar estoque por prazo local');
Schedule::command('saba:reconcile-payments')->everyMinute()->withoutOverlapping();

Artisan::command('saba:monitor-shipments {--limit=100}', function (): void {
    $count = app(\App\Actions\Shipping\DispatchShipmentMonitoringAction::class)->execute((int) $this->option('limit'));
    $this->info($count.' envio(s) enfileirado(s) para conferência sem compra de frete.');
})->purpose('Verificar automaticamente etiquetas, postagem e entrega no Melhor Envio');

Schedule::command('saba:monitor-shipments')->everyMinute()->withoutOverlapping(5);

Artisan::command('saba:sync-order-alerts {--limit=100}', function (): void {
    $count = app(\App\Actions\Notification\SyncOperationalOrderAlertsAction::class)->execute((int) $this->option('limit'));
    $this->info($count.' pedido(s) com situação operacional avaliados. Notificações já existentes não são duplicadas.');
})->purpose('Gerar avisos para pedidos antigos ainda aguardando nota ou impressão de etiqueta');

Schedule::command('saba:sync-order-alerts')->everyFifteenMinutes()->withoutOverlapping(5);

// Do not block a coupon forever for a freight quote abandoned before payment.
// Remote pending payments are excluded and keep their claims until a definitive state.
Artisan::command('saba:expire-unused-coupons', function (): void {
    $count = app(\App\Actions\Coupon\ExpireUnusedCouponReservationsAction::class)->execute();
    $this->info($count.' utilização(ões) de cupom abandonada(s) liberada(s).');
})->purpose('Liberar cupons de pedidos sem cobrança após 24 horas');
Schedule::command('saba:expire-unused-coupons')->everyTenMinutes()->withoutOverlapping();
