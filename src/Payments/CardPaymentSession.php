<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Payments;

/**
 * A started card payment: the holder's id of the payment, the URL of the
 * hosted payment page to send the customer to, and when that page runs out
 * (ISO 8601).
 */
final readonly class CardPaymentSession
{
    public function __construct(
        public string $paymentId,
        public string $url,
        public string $expiresAt,
    ) {}
}
