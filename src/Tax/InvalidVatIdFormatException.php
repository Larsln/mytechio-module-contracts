<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

use MyTechIO\Contracts\ContractException;

/**
 * Wird geworfen, wenn `countryCode`/`vatNumber` bereits formal ungültig
 * sind (z. B. Ländercode nicht zweistellig) — ein `VatIdChecker` prüft
 * dies, bevor er überhaupt eine Anfrage an den Dienst schickt.
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
