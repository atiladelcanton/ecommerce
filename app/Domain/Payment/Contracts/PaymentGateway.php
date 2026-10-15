<?php

declare(strict_types=1);

namespace App\Domain\Payment\Contracts;

use App\Domain\Payment\Data\PaymentRequest;
use App\Domain\Payment\Data\PaymentResult;

interface PaymentGateway
{
    public function createPayment(PaymentRequest $request): PaymentResult;
}
