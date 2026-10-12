<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * One booked depreciation of an asset for a fiscal year.
 *
 * `kind` is `annual` (the yearly run) or `disposal` (the pro-rata amount
 * booked when the asset left the register). `journalEntryId` is the ID of
 * the journal entry that carries the posting.
 */
final readonly class DepreciationEntryData
{
    public function __construct(
        public int $assetId,
        public int $fiscalYear,
        public string $amount,
        public string $kind,
        public ?string $journalEntryId,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'asset_id' => $this->assetId,
            'fiscal_year' => $this->fiscalYear,
            'amount' => $this->amount,
            'kind' => $this->kind,
            'journal_entry_id' => $this->journalEntryId,
        ];
    }
}
