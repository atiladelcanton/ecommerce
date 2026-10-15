<?php

declare(strict_types=1);

namespace App\Infrastructure\Payments;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Data\PaymentRequest;
use App\Domain\Payment\Data\PaymentResult;
use App\Domain\Payment\Enums\PaymentStatus;

final class FakePaymentGateway implements PaymentGateway
{
    public function createPayment(PaymentRequest $request): PaymentResult
    {
        return new PaymentResult('fake', 'fake-'.$request->idempotencyKey, PaymentStatus::APPROVED, true);
    }
}
