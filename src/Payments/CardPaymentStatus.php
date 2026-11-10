<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Payments;

/** Where a card payment stands. Only `Authorized` and `Captured` mean the customer has paid or will. */
enum CardPaymentStatus: string
{
    /** The customer is (or was) on the payment page and has not finished. */
    case Open = 'open';
    /** The amount is reserved on the card and can be collected. */
    case Authorized = 'authorized';
    /** The amount (or a part of it) was collected. */
    case Captured = 'captured';
    /** The reservation was released or the customer gave up. */
    case Canceled = 'canceled';
    /** The payment page or the reservation ran out. */
    case Expired = 'expired';
    case Failed = 'failed';

    /** Whether the money is safe: reserved or already collected. */
    public function isSecured(): bool
    {
        return $this === self::Authorized || $this === self::Captured;
    }

    /** Whether nothing more will happen to the payment without a new attempt. */
    public function isDead(): bool
    {
        return in_array($this, [self::Canceled, self::Expired, self::Failed], true);
    }
}
