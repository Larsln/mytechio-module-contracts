<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Units;

use MyTechIO\Contracts\ContractException;

/**
 * Read-only view of a core unit of measure.
 *
 * `code` is the short code printed on document lines and stored by modules
 * (e.g. `"Stk"`, `"Std"`), `name` its German display name (e.g. `"Stück"`),
 * `nameEn` the English one when maintained, `uneceCode` the UN/ECE
 * Recommendation 20 code (e.g. `"H87"`, `"HUR"`) or `null`, and `isDefault`
 * marks the unit the core pre-selects for new lines (at most one).
 */
final readonly class UnitData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $nameEn,
        public ?string $uneceCode,
        public bool $isDefault,
    ) {
        if (trim($code) === '') {
            throw new ContractException('UnitData: code must not be empty.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'name_en' => $this->nameEn,
            'unece_code' => $this->uneceCode,
            'is_default' => $this->isDefault,
        ];
    }
}
