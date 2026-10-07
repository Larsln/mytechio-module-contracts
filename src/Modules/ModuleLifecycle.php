<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Modules;

/**
 * Lifecycle hooks of a module, resolved by the core via the `lifecycle`
 * key in the module manifest (through the container).
 *
 * Order on first activation: module migrations → `onInstall()` →
 * `onEnable()`. On every subsequent activation, only `onEnable()`.
 * `Modules\AbstractModuleLifecycle` provides empty implementations to
 * extend when a module does not need all hooks.
 */
interface ModuleLifecycle
{
    /**
     * Called by the core on activation — after the module migrations.
     * May throw: the module then stays disabled and the error
     * appears on the module card.
     */
    public function onEnable(): void;

    /**
     * Called by the core on deactivation (scheduler/queue cleanup,
     * caches). Must not throw.
     */
    public function onDisable(): void;

    /**
     * Called once, when the module is activated for the first time in
     * this installation (before `onEnable()`).
     */
    public function onInstall(): void;

    /**
     * Only via `php artisan module:uninstall <name>` (phase 4) — removes
     * the module's data.
     */
    public function onUninstall(): void;
}
