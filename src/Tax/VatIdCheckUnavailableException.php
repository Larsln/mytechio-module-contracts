<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

use MyTechIO\Contracts\ContractException;

/**
 * Wird geworfen, wenn eine VIES-Prüfung nicht durchgeführt werden kann
 * (Dienst nicht erreichbar, Zeitüberschreitung, oder kein Modul mit einem
 * `VatIdChecker` aktiv). Der Kern behandelt dies als „VIES-Prüfung derzeit
 * nicht verfügbar" — die Steuerfindung läuft ohne das Prüfergebnis weiter
 * (Karenzregelung).
 */
final class VatIdCheckUnavailableException extends ContractException
{
    public function __construct(string $reason = 'Die VIES-Prüfung ist derzeit nicht erreichbar.')
    {
        parent::__construct($reason);
    }
}
