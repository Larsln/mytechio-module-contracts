<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Modules\ModuleState;

/**
 * Test double for `ModuleState`: by default EVERY module is active, so
 * module tests don't unintentionally stall. `deactivate()`/`activate()`
 * toggle a single module for the duration of the test.
 */
final class FakeModuleState implements ModuleState
{
    /**
     * @var array<string, bool>
     */
    private array $states = [];

    public function isActive(string $module): bool
    {
        return $this->states[$module] ?? true;
    }

    public function isEnabled(string $module): bool
    {
        return $this->states[$module] ?? true;
    }

    public function activate(string $module): self
    {
        $this->states[$module] = true;

        return $this;
    }

    public function deactivate(string $module): self
    {
        $this->states[$module] = false;

        return $this;
    }
}
