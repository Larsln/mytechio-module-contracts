<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * One row of the profile's rate table: the rate of a tax category in a country
 * from a date on (`validFrom`, ISO date). The user-maintainable `tax_rules`
 * table starts from these rows. `rate` is a decimal string, never a float.
 */
final readonly class TaxRateRow
{
    public function __construct(
        public string $countryCode,
        public string $taxCategoryCode,
        public string $rate,
        public string $validFrom,
        public ?string $notes = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'country_code' => $this->countryCode,
            'tax_category_code' => $this->taxCategoryCode,
            'rate' => $this->rate,
            'valid_from' => $this->validFrom,
            'notes' => $this->notes,
        ];
    }
}
