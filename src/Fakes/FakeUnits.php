<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Units\UnitData;
use MyTechIO\Contracts\Units\Units;

/**
 * Test double for `Units`: in-memory store, `seed()` adds units in the
 * order `all()` returns them. Seeding a second default replaces the
 * first one's flag, like the core's single-default rule.
 */
final class FakeUnits implements Units
{
    /**
     * @var array<string, UnitData>
     */
    private array $units = [];

    public function seed(UnitData $unit): void
    {
        if ($unit->isDefault) {
            foreach ($this->units as $code => $existing) {
                if ($existing->isDefault) {
                    $this->units[$code] = new UnitData($existing->code, $existing->name, $existing->nameEn, $existing->uneceCode, false);
                }
            }
        }

        $this->units[$unit->code] = $unit;
    }

    public function all(): array
    {
        return array_values($this->units);
    }

    public function find(string $code): ?UnitData
    {
        return $this->units[$code] ?? null;
    }

    public function default(): ?UnitData
    {
        foreach ($this->units as $unit) {
            if ($unit->isDefault) {
                return $unit;
            }
        }

        return null;
    }
}
