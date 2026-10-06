<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

/**
 * VIES-Prüfung einer USt-ID. Ein Modul bindet höchstens eine Implementierung
 * (kein Tag) — ohne aktives Modul bindet der Kern einen Null-Client, der
 * stets `VatIdCheckUnavailableException` wirft.
 */
interface VatIdChecker
{
    /**
     * @throws InvalidVatIdFormatException
     * @throws VatIdCheckUnavailableException
     */
    public function check(string $countryCode, string $vatNumber): VatIdCheckResult;
}
