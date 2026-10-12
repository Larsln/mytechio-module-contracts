<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * Whether the place-of-supply rules treat a customer as a business: an EU
 * customer only with a currently valid VAT ID, a third-country customer only
 * with a recorded proof of business status (`BusinessWithoutProof` is taxed
 * like a private customer).
 */
enum CustomerStatus: string
{
    case Business = 'business';
    case Consumer = 'consumer';
    case BusinessWithoutProof = 'business_without_proof';

    public function isBusiness(): bool
    {
        return $this === self::Business;
    }
}
