<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

use MyTechIO\Contracts\ContractException;

/**
 * Wird geworfen, wenn für `bookedOn` bereits eine festgeschriebene Periode
 * existiert (Monatsabschluss/Freigabe) — Korrekturen sind dann nur per
 * Gegenbuchung (`Journal::reverse()`) in einer offenen Periode möglich.
 */
final class PeriodClosedException extends ContractException
{
    public function __construct(
        public readonly string $bookedOn,
    ) {
        parent::__construct(sprintf('Die Buchungsperiode für %s ist festgeschrieben.', $bookedOn));
    }
}
