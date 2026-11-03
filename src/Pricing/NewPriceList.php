<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Pricing;

/**
 * Input of `PriceLists::create()` and `PriceLists::update()`: the complete
 * header of a price list (an update replaces name, description and status).
 */
final readonly class NewPriceList
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public bool $isActive = true,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }
}
