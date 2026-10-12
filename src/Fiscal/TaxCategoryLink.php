<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A link between a tax category and an account of the chart.
 */
final readonly class TaxCategoryLink
{
    public function __construct(
        public string $taxCategoryCode,
        public string $accountNumber,
        public TaxCategoryLinkKind $kind,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tax_category_code' => $this->taxCategoryCode,
            'account_number' => $this->accountNumber,
            'kind' => $this->kind->value,
        ];
    }
}
