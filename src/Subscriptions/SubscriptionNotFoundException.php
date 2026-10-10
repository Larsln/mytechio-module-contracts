<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a `Subscriptions` method is called with an unknown ID.
 */
final class SubscriptionNotFoundException extends ContractException
{
    public function __construct(
        public readonly int $id,
    ) {
        parent::__construct(sprintf('Leistung mit ID %d wurde nicht gefunden.', $id));
    }
}
