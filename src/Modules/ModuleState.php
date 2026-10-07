<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Modules;

/**
 * Laufzeit-Zustand eines Moduls, den Hintergrund-Code (Scheduler-Einträge,
 * Listener, Jobs) vor jeder Ausführung prüft.
 *
 * `isActive()` ist die Prüfung, die Hintergrund-Code verwenden muss: nur ein
 * bekanntes, aktiviertes UND kompatibles Modul darf Routen/Slots/
 * Hintergrund-Code laufen lassen. `isEnabled()` liefert ausschließlich den
 * Schalter aus der Modultabelle, ohne Kompatibilitätsprüfung — das ist
 * i. d. R. NICHT die richtige Prüfung für Hintergrund-Code.
 */
interface ModuleState
{
    /**
     * Modul bekannt, aktiviert UND kompatibel (= Routen/Slots/Hintergrund
     * dürfen laufen).
     */
    public function isActive(string $module): bool;

    /**
     * Nur der Schalter aus der Modultabelle, ohne Kompatibilitätsprüfung.
     */
    public function isEnabled(string $module): bool;
}
