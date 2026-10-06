<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Modules;

/**
 * Lebenszyklus-Hooks eines Moduls, vom Kern über den `lifecycle`-Schlüssel
 * im Modul-Manifest aufgelöst (per Container).
 *
 * Reihenfolge beim ersten Aktivieren: Modul-Migrationen → `onInstall()` →
 * `onEnable()`. Bei jedem weiteren Aktivieren nur `onEnable()`.
 * `Modules\AbstractModuleLifecycle` liefert leere Implementierungen zum
 * Erben, wenn ein Modul nicht alle Hooks braucht.
 */
interface ModuleLifecycle
{
    /**
     * Vom Kern beim Aktivieren aufgerufen — nach den Modul-Migrationen.
     * Darf werfen: dann bleibt das Modul deaktiviert und der Fehler
     * erscheint in der Modulkarte.
     */
    public function onEnable(): void;

    /**
     * Vom Kern beim Deaktivieren aufgerufen (Scheduler-/Queue-Aufräumen,
     * Caches). Darf nicht werfen.
     */
    public function onDisable(): void;

    /**
     * Einmalig, wenn das Modul zum ersten Mal in der Installation aktiviert
     * wird (vor `onEnable()`).
     */
    public function onInstall(): void;

    /**
     * Nur über `php artisan module:uninstall <name>` (Phase 4) — Daten des
     * Moduls entfernen.
     */
    public function onUninstall(): void;
}
