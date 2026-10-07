<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a journal entry draft is not balanced
 * (sum of debits ≠ sum of credits). Both the core and the `FakeJournal`
 * enforce this invariant.
 */
final class UnbalancedEntryException extends ContractException
{
    public function __construct(
        public readonly string $debitTotal,
        public readonly string $creditTotal,
    ) {
        parent::__construct(sprintf(
            'Buchungssatz ist nicht ausgeglichen: Soll %s ≠ Haben %s.',
            $debitTotal,
            $creditTotal,
        ));
    }
}
