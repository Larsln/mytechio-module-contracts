<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Modules\ModuleState;

/**
 * Test-Double für `ModuleState`: standardmäßig ist JEDES Modul aktiv, damit
 * Modul-Tests nicht unbeabsichtigt stillstehen. `deactivate()`/`activate()`
 * schalten ein einzelnes Modul für die Dauer des Tests um.
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
