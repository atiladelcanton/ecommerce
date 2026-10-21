<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Payment\ProcessAsaasCheckoutEventAction;
use App\Domain\Payment\Models\PaymentEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessAsaasWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 8;

    /** @var list<int> */
    public array $backoff = [10, 30, 60, 120, 300, 600, 900];

    public function __construct(public readonly int $eventId) {}

    public function handle(ProcessAsaasCheckoutEventAction $action): void
    {
        $action->execute(PaymentEvent::query()->findOrFail($this->eventId));
    }
}
