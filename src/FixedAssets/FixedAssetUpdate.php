<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * Input of `FixedAssets::update()`: the complete set of editable fields, the
 * asset is replaced by it. From the first associated posting on, acquisition
 * cost, acquisition date, kind and ledger account must stay unchanged
 * (`ContractException` otherwise); name, category, notes, useful life,
 * depreciation start and salvage value remain editable.
 * `depreciationStart` defaults to the acquisition date, `salvageValue` to 0.
 */
final readonly class FixedAssetUpdate
{
    public function __construct(
        public string $name,
        public FixedAssetKind $kind,
        public string $acquisitionDate,
        public string $acquisitionCost,
        public ?string $category = null,
        public ?int $usefulLifeYears = null,
        public ?string $depreciationStart = null,
        public ?string $salvageValue = null,
        public ?string $ledgerAccountNumber = null,
        public ?string $notes = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'kind' => $this->kind->value,
            'acquisition_date' => $this->acquisitionDate,
            'acquisition_cost' => $this->acquisitionCost,
            'category' => $this->category,
            'useful_life_years' => $this->usefulLifeYears,
            'depreciation_start' => $this->depreciationStart,
            'salvage_value' => $this->salvageValue,
            'ledger_account_number' => $this->ledgerAccountNumber,
            'notes' => $this->notes,
        ];
    }
}
