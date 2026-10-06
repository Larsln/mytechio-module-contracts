<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Settings;

/**
 * Modulspezifische Einstellungen, persistiert im Kern.
 *
 * Werte werden pro Modul (`$module`, z. B. `'domainrobot'`) und Schlüssel
 * (`$key`) gehalten. `isFromEnvironment()` meldet, ob ein Wert aus der
 * Umgebung/Config kommt und daher Vorrang vor der Datenbank hat — die
 * Implementierung lässt `set()` auf solche Schlüssel dann wirkungslos
 * verlaufen bzw. die UI sperrt das Feld. `encrypted` markiert sensible
 * Werte (z. B. Passwörter) zur verschlüsselten Ablage.
 *
 * Implementierung erst Phase 3 — hier nur Interface + Fake.
 */
interface ModuleSettings
{
    public function get(string $module, string $key, mixed $default = null): mixed;

    public function set(string $module, string $key, mixed $value, bool $encrypted = false): void;

    public function forget(string $module, string $key): void;

    public function isFromEnvironment(string $module, string $key): bool;
}
