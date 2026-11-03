<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Pricing;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a `PriceLists` method is called with an unknown price list,
 * entry, contact or price option ID.
 */
final class PriceListNotFoundException extends ContractException
{
    public function __construct(
        public readonly string $subject,
        public readonly int $id,
    ) {
        parent::__construct(sprintf('%s mit ID %d wurde nicht gefunden.', $subject, $id));
    }
}
