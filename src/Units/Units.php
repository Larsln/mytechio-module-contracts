<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Units;

/**
 * Read-only access to the core's units of measure (the master data behind
 * the unit picker on document lines).
 *
 * Modules show the same units as the core and store the unit code; they
 * never create or change units — unit master data remains exclusively the
 * core's responsibility.
 */
interface Units
{
    /**
     * Every unit in the order the core maintains for its unit picker.
     *
     * @return list<UnitData>
     */
    public function all(): array;

    /**
     * Looks up a unit by its code (exact, case-sensitive); `null` when unknown.
     */
    public function find(string $code): ?UnitData;

    /**
     * The unit the core pre-selects for new lines, `null` when none is marked.
     */
    public function default(): ?UnitData;
}
