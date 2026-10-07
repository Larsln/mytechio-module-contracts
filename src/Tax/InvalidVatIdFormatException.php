<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when `countryCode`/`vatNumber` are already invalid in format
 * (e.g. the country code is not two characters) — a `VatIdChecker` checks
 * this before it even sends a request to the service.
 */
final class InvalidVatIdFormatException extends ContractException
{
    public function __construct(
        public readonly string $countryCode,
        public readonly string $vatNumber,
    ) {
        parent::__construct(sprintf('Ungültiges Format der USt-ID: %s%s.', $countryCode, $vatNumber));
    }
}
