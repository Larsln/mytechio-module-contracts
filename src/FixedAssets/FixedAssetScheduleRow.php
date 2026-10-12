<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * One row of the fixed-asset schedule (Anlagespiegel) of a calendar year, in
 * gross presentation: acquisition cost, addition and disposal in the year,
 * accumulated depreciation at the start and the end, the year's depreciation
 * and the book values at both ends (4 decimal places).
 */
final readonly class FixedAssetScheduleRow
{
    public function __construct(
        public int $assetId,
        public ?string $inventoryNumber,
        public string $name,
        public FixedAssetKind $kind,
        public ?string $accountNumber,
        public ?string $accountName,
        public bool $isActivated,
        public string $cost,
        public ?string $addition,
        public ?string $disposal,
        public string $accumulatedStart,
        public string $depreciation,
        public string $accumulatedEnd,
        public string $bookStart,
        public string $bookEnd,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'asset_id' => $this->assetId,
            'inventory_number' => $this->inventoryNumber,
            'name' => $this->name,
            'kind' => $this->kind->value,
            'account_number' => $this->accountNumber,
            'account_name' => $this->accountName,
            'is_activated' => $this->isActivated,
            'cost' => $this->cost,
            'addition' => $this->addition,
            'disposal' => $this->disposal,
            'accumulated_start' => $this->accumulatedStart,
            'depreciation' => $this->depreciation,
            'accumulated_end' => $this->accumulatedEnd,
            'book_start' => $this->bookStart,
            'book_end' => $this->bookEnd,
        ];
    }
}
