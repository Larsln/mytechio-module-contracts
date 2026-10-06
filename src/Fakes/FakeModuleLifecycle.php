<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Modules\ModuleLifecycle;

/**
 * Test-Double für `ModuleLifecycle`: zählt Aufrufe je Hook.
 * `failOnEnable()` lässt `onEnable()` werfen, um das Fehlerverhalten des
 * Kerns (Modul bleibt deaktiviert) in Modul-Tests nachzustellen.
 */
final class FakeModuleLifecycle implements ModuleLifecycle
{
    public int $enableCalls = 0;

    public int $disableCalls = 0;

    public int $installCalls = 0;

    public int $uninstallCalls = 0;

    private bool $shouldFailOnEnable = false;

    public function onEnable(): void
    {
        $this->enableCalls++;

        if ($this->shouldFailOnEnable) {
            throw new ContractException('onEnable fehlgeschlagen (Fake).');
        }
    }

    public function onDisable(): void
    {
        $this->disableCalls++;
    }

    public function onInstall(): void
    {
        $this->installCalls++;
    }

    public function onUninstall(): void
    {
        $this->uninstallCalls++;
    }

    public function failOnEnable(): void
    {
        $this->shouldFailOnEnable = true;
    }
}
