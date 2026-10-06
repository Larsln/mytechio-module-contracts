<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Modules;

/**
 * Leere Standard-Implementierung von `ModuleLifecycle` zum Erben — Module
 * überschreiben nur die Hooks, die sie tatsächlich benötigen.
 */
abstract class AbstractModuleLifecycle implements ModuleLifecycle
{
    public function onEnable(): void {}

    public function onDisable(): void {}

    public function onInstall(): void {}

    public function onUninstall(): void {}
}
