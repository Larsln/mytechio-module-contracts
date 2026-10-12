<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A tax category the profile seeds (the user-maintainable `tax_categories`
 * table starts from these): its code, name and default rate in the profile's
 * own country.
 */
final readonly class TaxCategoryInfo
{
    public function __construct(
        public string $code,
        public string $name,
        public string $defaultRate,
        public bool $isActive = true,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'default_rate' => $this->defaultRate,
            'is_active' => $this->isActive,
        ];
    }
}
