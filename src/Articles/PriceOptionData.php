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
 *
 * The prices are per price period: by default the billing period itself
 * (`pricePeriodUnit` and `pricePeriodCount` are `null`), otherwise the
 * smaller period they name, e.g. a price per month (`"month"`, 1) billed
 * every 3 months — the invoice then shows 3 months × the monthly price. The
 * billing period is always a whole multiple of the price period.
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
        public ?string $pricePeriodUnit = null,
        public ?int $pricePeriodCount = null,
    ) {
        Validate::decimal($priceB2cNet, 'priceB2cNet');
        Validate::decimal($priceB2bNet, 'priceB2bNet');

        $hasPeriod = $intervalUnit !== null || $intervalCount !== null;
        $hasPricePeriod = $pricePeriodUnit !== null || $pricePeriodCount !== null;

        if ($priceForm === PriceForm::OneOff && ($hasPeriod || $hasPricePeriod)) {
            throw new ContractException('A one-off price option must not carry an interval or a price period.');
        }

        if ($priceForm === PriceForm::PerPeriod) {
            if ($intervalUnit === null || $intervalCount === null) {
                throw new ContractException('A per-period price option needs intervalUnit and intervalCount.');
            }

            self::validatePeriod($intervalUnit, $intervalCount, 'intervalUnit', 'intervalCount');

            if ($hasPricePeriod) {
                if ($pricePeriodUnit === null || $pricePeriodCount === null) {
                    throw new ContractException('pricePeriodUnit and pricePeriodCount must either both be set or both be null.');
                }

                self::validatePeriod($pricePeriodUnit, $pricePeriodCount, 'pricePeriodUnit', 'pricePeriodCount');
            }
        }
    }

    private static function validatePeriod(string $unit, int $count, string $unitField, string $countField): void
    {
        if (! in_array($unit, ['day', 'week', 'month', 'year'], true)) {
            throw new ContractException("{$unitField} must be 'day', 'week', 'month' or 'year', got \"{$unit}\".");
        }

        if ($count < 1) {
            throw new ContractException("{$countField} must be at least 1.");
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
            'price_period_unit' => $this->pricePeriodUnit,
            'price_period_count' => $this->pricePeriodCount,
        ];
    }
}
