<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when an `Orders` method is called with an unknown order ID.
 */
final class OrderNotFoundException extends ContractException
{
    public function __construct(
        public readonly int $id,
    ) {
        parent::__construct(sprintf('Auftrag mit ID %d wurde nicht gefunden.', $id));
    }
}
