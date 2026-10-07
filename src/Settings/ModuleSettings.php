<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Settings;

/**
 * Module-specific settings, persisted in the core.
 *
 * Values are kept per module (`$module`, e.g. `'domainrobot'`) and key
 * (`$key`). `isFromEnvironment()` reports whether a value comes from the
 * environment/config and therefore takes precedence over the database —
 * the implementation then makes `set()` a no-op for such keys, or the UI
 * locks the field. `encrypted` marks sensitive values (e.g. passwords)
 * for encrypted storage.
 *
 * Implementation not until phase 3 — only the interface and fake exist so far.
 */
interface ModuleSettings
{
    public function get(string $module, string $key, mixed $default = null): mixed;

    public function set(string $module, string $key, mixed $value, bool $encrypted = false): void;

    public function forget(string $module, string $key): void;

    public function isFromEnvironment(string $module, string $key): bool;
}
