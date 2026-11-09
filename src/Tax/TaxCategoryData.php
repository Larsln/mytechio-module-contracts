<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

use MyTechIO\Contracts\Invoices\Validate;

/**
 * Read-only view of a core tax category.
 *
 * `rate` is the VAT rate in percent as a decimal string with two places
 * (e.g. `"19.00"`), `isActive` tells whether the core still offers the
 * category for new documents and `label` is the display label exactly as the
 * core shows it (e.g. „19 % (Standard)“).
 */
final readonly class TaxCategoryData
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public string $rate,
        public bool $isActive,
        public string $label,
    ) {
        Validate::decimal($rate, 'rate');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'rate' => $this->rate,
            'is_active' => $this->isActive,
            'label' => $this->label,
        ];
    }
}
