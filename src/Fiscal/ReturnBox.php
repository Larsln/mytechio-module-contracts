<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A box (field) of the VAT return: number, label and how its tax base and tax
 * are summed from the journal's tax codes. `{rate}` in the label stands for the
 * rate key. Rate keys are found in the journal under `rateKeyPrefixes` (minus
 * `excludedRateKeys`); a per-rate box takes its number and label from the
 * matching variant and falls back to its own. With `cashBase`, the base on the
 * cash basis is back-calculated from the tax instead of summed from `base`.
 */
final readonly class ReturnBox
{
    /**
     * @param  list<string>  $rateKeyPrefixes
     * @param  list<string>  $excludedRateKeys
     * @param  list<ReturnRateVariant>  $rateVariants
     */
    public function __construct(
        public string $kz,
        public string $label,
        public ?ReturnAmount $base = null,
        public ?ReturnAmount $tax = null,
        public ReturnBoxGrouping $grouping = ReturnBoxGrouping::Single,
        public array $rateKeyPrefixes = [],
        public array $excludedRateKeys = [],
        public array $rateVariants = [],
        public ?ReturnAmount $cashBase = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'kz' => $this->kz,
            'label' => $this->label,
            'base' => $this->base?->toArray(),
            'tax' => $this->tax?->toArray(),
            'grouping' => $this->grouping->value,
            'rate_key_prefixes' => $this->rateKeyPrefixes,
            'excluded_rate_keys' => $this->excludedRateKeys,
            'rate_variants' => array_map(fn (ReturnRateVariant $variant): array => $variant->toArray(), $this->rateVariants),
            'cash_base' => $this->cashBase?->toArray(),
        ];
    }
}
