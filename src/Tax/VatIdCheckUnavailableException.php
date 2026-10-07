<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a VIES check cannot be performed (service unreachable,
 * timeout, or no module with a `VatIdChecker` active). The core treats
 * this as "VIES check currently unavailable" — tax determination
 * continues without the check result (grace period rule).
 */
final class VatIdCheckUnavailableException extends ContractException
{
    public function __construct(string $reason = 'Die VIES-Prüfung ist derzeit nicht erreichbar.')
    {
        parent::__construct($reason);
    }
}
