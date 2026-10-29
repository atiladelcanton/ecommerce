<?php

declare(strict_types=1);

namespace Tests\Unit\Payment;

use App\Domain\Payment\Models\Payment;
use Carbon\CarbonInterface;
use PHPUnit\Framework\TestCase;

final class PaymentCastsTest extends TestCase
{
    public function test_asaas_payment_dates_are_datetime_objects_even_when_loaded_as_strings(): void
    {
        $payment = new Payment();
        $payment->setRawAttributes([
            'expires_at' => '2026-10-29 13:30:00',
            'last_checked_at' => '2026-10-29 13:31:00',
            'needs_review' => 1,
        ]);

        self::assertInstanceOf(CarbonInterface::class, $payment->expires_at);
        self::assertSame('29/10/2026 13:30', $payment->expires_at->format('d/m/Y H:i'));
        self::assertInstanceOf(CarbonInterface::class, $payment->last_checked_at);
        self::assertTrue($payment->needs_review);
    }

    public function test_nullable_expiration_and_review_flag_work(): void
    {
        $payment = new Payment();
        $payment->setRawAttributes(['expires_at' => null, 'needs_review' => 0]);

        self::assertNull($payment->expires_at);
        self::assertFalse($payment->needs_review);
    }

    public function test_asaas_metadata_is_mass_assignable_for_existing_update_actions(): void
    {
        $payment = new Payment([
            'environment' => 'sandbox',
            'expires_at' => '2026-10-29 13:30:00',
            'needs_review' => true,
            'review_reason' => 'Aguardando conferência',
            'last_checked_at' => '2026-10-29 13:00:00',
        ]);

        self::assertSame('sandbox', $payment->environment);
        self::assertTrue($payment->needs_review);
        self::assertSame('Aguardando conferência', $payment->review_reason);
        self::assertSame('29/10/2026 13:30', $payment->expires_at->format('d/m/Y H:i'));
    }
}
