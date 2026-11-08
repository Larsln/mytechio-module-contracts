<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a `Subscriptions` group method is called with an unknown group ID.
 */
final class SubscriptionGroupNotFoundException extends ContractException
{
    public function __construct(
        public readonly int $id,
    ) {
        parent::__construct(sprintf('Leistungsgruppe mit ID %d wurde nicht gefunden.', $id));
    }
}
