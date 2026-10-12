<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * One asset in the open depreciation of a fiscal year: the asset account it
 * is credited on, the expense account it is debited on and the amount.
 */
final readonly class DepreciationPreviewRow
{
    public function __construct(
        public int $assetId,
        public string $inventoryNumber,
        public string $name,
        public string $accountNumber,
        public string $expenseAccountNumber,
        public string $amount,
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
            'account_number' => $this->accountNumber,
            'expense_account_number' => $this->expenseAccountNumber,
            'amount' => $this->amount,
        ];
    }
}
