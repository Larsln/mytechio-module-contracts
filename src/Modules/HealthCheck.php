<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Modules;

/**
 * Gesundheitsstatus eines Moduls für die Modulübersicht.
 *
 * Der Kern ruft `health()` NUR auf der Modulübersicht auf und mit
 * Timeout-Schutz (try/catch ⇒ `HealthStatus::error()`); Implementierungen
 * dürfen deshalb keine langsamen Netzaufrufe machen (max. Cache/DB).
 */
interface HealthCheck
{
    public function health(): HealthStatus;
}
