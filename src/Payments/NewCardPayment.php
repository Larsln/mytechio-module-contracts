<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Payments;

use MyTechIO\Contracts\Invoices\Validate;

/**
 * Input of `CardPayments::authorize()`: the customer pays `amount` (decimal
 * string, two decimals, in the organization's currency) by card on the
 * payment provider's hosted page. The money is only reserved; the holder of
 * the capability collects it later.
 *
 * `reference` is the caller's own reference of the purchase (unique per
 * attempt, e.g. a checkout reference); it is how the caller finds the
 * payment again. `successUrl` and `cancelUrl` are where the provider sends
 * the customer back; `description` is the one line the customer sees there.
 */
final readonly class NewCardPayment
{
    public function __construct(
        public string $reference,
        public int $contactId,
        public string $amount,
        public string $description,
        public string $successUrl,
        public string $cancelUrl,
    ) {
        Validate::decimal($amount, 'amount');
    }
}
