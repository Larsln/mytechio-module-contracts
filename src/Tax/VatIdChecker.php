<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

/**
 * VIES check of a VAT ID (Umsatzsteuer-Identifikationsnummer). A module
 * binds at most one implementation (no tag) — without an active module,
 * the core binds a null client that always throws
 * `VatIdCheckUnavailableException`.
 */
interface VatIdChecker
{
    /**
     * @throws InvalidVatIdFormatException
     * @throws VatIdCheckUnavailableException
     */
    public function check(string $countryCode, string $vatNumber): VatIdCheckResult;
}
