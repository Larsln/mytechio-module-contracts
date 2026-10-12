<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * A fixed asset of the company's register.
 *
 * Amounts are strings with 4 decimal places, dates are `Y-m-d`, timestamps
 * ISO-8601. `inventoryNumber` is `null` for `ListOnly` assets (they draw no
 * number from the gapless range). `ledgerAccountNumber` is the 0xxx account
 * the asset is capitalized on (`null` while not activated). `isActivated`
 * means the acquisition cost sits on that account; `isJournalLinked` is true
 * from the first associated posting on — the booking-relevant fields
 * (acquisition cost and date, kind, account) are then fixed and the asset
 * leaves the register only through a disposal or the cancellation of its
 * source document. `bookValue` and `accumulatedDepreciation` are as of the
 * day the data was read.
 */
final readonly class FixedAssetData
{
    /**
     * @param  list<DepreciationEntryData>  $depreciationEntries
     */
    public function __construct(
        public int $id,
        public ?string $inventoryNumber,
        public string $name,
        public FixedAssetKind $kind,
        public ?string $category,
        public string $acquisitionDate,
        public string $acquisitionCost,
        public ?int $usefulLifeYears,
        public ?string $depreciationStart,
        public string $salvageValue,
        public FixedAssetStatus $status,
        public ?string $disposedOn,
        public ?string $disposalNote,
        public ?string $ledgerAccountNumber,
        public ?int $incomingInvoiceId,
        public ?int $incomingInvoiceItemId,
        public bool $isActivated,
        public bool $isJournalLinked,
        public ?string $notes,
        public string $bookValue,
        public string $accumulatedDepreciation,
        public ?int $createdBy = null,
        public ?string $createdAt = null,
        public array $depreciationEntries = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'inventory_number' => $this->inventoryNumber,
            'name' => $this->name,
            'kind' => $this->kind->value,
            'category' => $this->category,
            'acquisition_date' => $this->acquisitionDate,
            'acquisition_cost' => $this->acquisitionCost,
            'useful_life_years' => $this->usefulLifeYears,
            'depreciation_start' => $this->depreciationStart,
            'salvage_value' => $this->salvageValue,
            'status' => $this->status->value,
            'disposed_on' => $this->disposedOn,
            'disposal_note' => $this->disposalNote,
            'ledger_account_number' => $this->ledgerAccountNumber,
            'incoming_invoice_id' => $this->incomingInvoiceId,
            'incoming_invoice_item_id' => $this->incomingInvoiceItemId,
            'is_activated' => $this->isActivated,
            'is_journal_linked' => $this->isJournalLinked,
            'notes' => $this->notes,
            'book_value' => $this->bookValue,
            'accumulated_depreciation' => $this->accumulatedDepreciation,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'depreciation_entries' => array_map(
                static fn (DepreciationEntryData $entry): array => $entry->toArray(),
                $this->depreciationEntries,
            ),
        ];
    }
}
