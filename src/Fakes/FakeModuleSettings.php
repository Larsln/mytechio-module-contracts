<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Settings\ModuleSettings;

/**
 * Test double for `ModuleSettings`: in-memory store.
 * `markFromEnvironment()` simulates a key that, in the real
 * implementation, comes from the environment/config and therefore takes
 * precedence over the database.
 */
final class FakeModuleSettings implements ModuleSettings
{
    /**
     * @var array<string, mixed>
     */
    private array $values = [];

    /**
     * @var array<string, true>
     */
    private array $fromEnvironment = [];

    public function get(string $module, string $key, mixed $default = null): mixed
    {
        return $this->values[$this->keyFor($module, $key)] ?? $default;
    }

    public function set(string $module, string $key, mixed $value, bool $encrypted = false): void
    {
        $this->values[$this->keyFor($module, $key)] = $value;
    }

    public function forget(string $module, string $key): void
    {
        unset($this->values[$this->keyFor($module, $key)]);
    }

    public function isFromEnvironment(string $module, string $key): bool
    {
        return isset($this->fromEnvironment[$this->keyFor($module, $key)]);
    }

    public function markFromEnvironment(string $module, string $key): void
    {
        $this->fromEnvironment[$this->keyFor($module, $key)] = true;
    }

    private function keyFor(string $module, string $key): string
    {
        return $module.'.'.$key;
    }
}
