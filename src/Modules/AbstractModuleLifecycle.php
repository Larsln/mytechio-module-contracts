<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Modules;

/**
 * Empty default implementation of `ModuleLifecycle` to extend — modules
 * only override the hooks they actually need.
 */
abstract class AbstractModuleLifecycle implements ModuleLifecycle
{
    public function onEnable(): void {}

    public function onDisable(): void {}

    public function onInstall(): void {}

    public function onUninstall(): void {}
}
