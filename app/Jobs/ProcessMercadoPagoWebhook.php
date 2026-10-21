<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Payment\ReconcileHostedPaymentAction;
use App\Domain\Payment\Models\PaymentEvent;
use App\Domain\Payment\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessMercadoPagoWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 6;

    /** @var list<int> */
    public array $backoff = [30, 60, 120, 300, 600];

    public function __construct(public readonly int $eventId) {}

    public function handle(ReconcileHostedPaymentAction $action): void
    {
        $event = PaymentEvent::query()->findOrFail($this->eventId);
        if ($event->processed_at) {
            return;
        }
        $payment = Payment::query()->where('gateway', $event->gateway)->where('environment', $event->environment)
            ->where('external_id', $event->external_id)->first();
        if (! $payment) {
            throw new \RuntimeException('Cobrança ainda não vinculada. Reprocessar a notificação.');
        }
        $action->execute($payment);
        $event->update(['payment_id' => $payment->id, 'processed_at' => now()]);
    }
}
