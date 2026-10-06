<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

use MyTechIO\Contracts\ContractException;

/**
 * Wird geworfen, wenn ein Buchungssatz-Entwurf nicht ausgeglichen ist
 * (Summe Soll ≠ Summe Haben). Sowohl der Kern als auch der `FakeJournal`
 * erzwingen diese Invariante.
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
