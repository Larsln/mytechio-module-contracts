<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Invoices\Validate;

/**
 * Read-only view of one price option of an article.
 *
 * An article always has at least one price option and exactly one default. A
 * `OneOff` option has no period (`intervalUnit` and `intervalCount` are
 * `null`); a `PerPeriod` option is billed every `intervalCount` ×
 * `intervalUnit` (`"day"`, `"week"`, `"month"` or `"year"`). The prices are
 * decimal strings with up to 4 decimals, never floats; derived options
 * (e.g. a yearly price computed from the monthly one) arrive with their
 * current computed prices.
 */
final readonly class PriceOptionData
{
    public function __construct(
        public int $id,
        public string $label,
        public PriceForm $priceForm,
        public ?string $intervalUnit,
        public ?int $intervalCount,
        public string $priceB2cNet,
        public string $priceB2bNet,
        public bool $isDefault,
    ) {
        Validate::decimal($priceB2cNet, 'priceB2cNet');
        Validate::decimal($priceB2bNet, 'priceB2bNet');

        $hasPeriod = $intervalUnit !== null || $intervalCount !== null;

        if ($priceForm === PriceForm::OneOff && $hasPeriod) {
            throw new ContractException('A one-off price option must not carry an interval.');
        }

        if ($priceForm === PriceForm::PerPeriod) {
            if ($intervalUnit === null || $intervalCount === null) {
                throw new ContractException('A per-period price option needs intervalUnit and intervalCount.');
            }

            if (! in_array($intervalUnit, ['day', 'week', 'month', 'year'], true)) {
                throw new ContractException("intervalUnit must be 'day', 'week', 'month' or 'year', got \"{$intervalUnit}\".");
            }

            if ($intervalCount < 1) {
                throw new ContractException('intervalCount must be at least 1.');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'price_form' => $this->priceForm->value,
            'interval_unit' => $this->intervalUnit,
            'interval_count' => $this->intervalCount,
            'price_b2c_net' => $this->priceB2cNet,
            'price_b2b_net' => $this->priceB2bNet,
            'is_default' => $this->isDefault,
        ];
    }
}
