<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when an `Accounts` write method is called with an unknown account ID.
 */
final class AccountNotFoundException extends ContractException
{
    public function __construct(
        public readonly int $id,
    ) {
        parent::__construct(sprintf('Konto mit ID %d wurde nicht gefunden.', $id));
    }
}
