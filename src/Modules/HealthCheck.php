<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Modules;

/**
 * Health status of a module for the module overview.
 *
 * The core calls `health()` ONLY on the module overview, and with
 * timeout protection (try/catch ⇒ `HealthStatus::error()`); implementations
 * must therefore not make slow network calls (cache/DB at most).
 */
interface HealthCheck
{
    public function health(): HealthStatus;
}
