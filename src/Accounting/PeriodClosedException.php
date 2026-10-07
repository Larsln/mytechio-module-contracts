<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a closed period already exists for `bookedOn`
 * (month-end close/approval) — corrections are then only possible via
 * a reversing entry (`Journal::reverse()`) in an open period.
 */
final class PeriodClosedException extends ContractException
{
    public function __construct(
        public readonly string $bookedOn,
    ) {
        parent::__construct(sprintf('Die Buchungsperiode für %s ist festgeschrieben.', $bookedOn));
    }
}
