<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Modules;

/**
 * Runtime state of a module, checked by background code (scheduler
 * entries, listeners, jobs) before every execution.
 *
 * `isActive()` is the check that background code must use: only a
 * known, enabled AND compatible module may run routes/slots/
 * background code. `isEnabled()` returns only the switch from the
 * module table, without a compatibility check — this is usually
 * NOT the right check for background code.
 */
interface ModuleState
{
    /**
     * Module is known, enabled AND compatible (= routes/slots/background
     * code may run).
     */
    public function isActive(string $module): bool;

    /**
     * Only the switch from the module table, without a compatibility check.
     */
    public function isEnabled(string $module): bool;
}
