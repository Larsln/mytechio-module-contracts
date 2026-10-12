<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

/**
 * A stock voucher (Lagerbeleg, number like `LB-2026-00001`) with the
 * movements it carries. `ledgerEntryId` is the journal entry the voucher was
 * booked on (`null` when it has no booking effect); `bookingNote` explains why
 * a write-off was not booked. A reversal voucher names the voucher it cancels
 * in `reversesNumber`, a cancelled voucher names its counter-voucher in
 * `reversedByNumber`. `voucherDate` is `Y-m-d`, `createdAt` ISO 8601.
 */
final readonly class StockVoucherData
{
    /**
     * @param  list<StockMovementData>  $movements
     */
    public function __construct(
        public int $id,
        public string $number,
        public StockVoucherType $type,
        public string $voucherDate,
        public string $reason,
        public ?string $bookingNote,
        public ?string $ledgerEntryId,
        public ?string $reversesNumber,
        public ?string $reversedByNumber,
        public ?int $createdById,
        public ?string $createdAt,
        public array $movements = [],
    ) {}

    public function isReversal(): bool
    {
        return $this->reversesNumber !== null;
    }

    public function isReversed(): bool
    {
        return $this->reversedByNumber !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'type' => $this->type->value,
            'voucher_date' => $this->voucherDate,
            'reason' => $this->reason,
            'booking_note' => $this->bookingNote,
            'ledger_entry_id' => $this->ledgerEntryId,
            'reverses_number' => $this->reversesNumber,
            'reversed_by_number' => $this->reversedByNumber,
            'created_by_id' => $this->createdById,
            'created_at' => $this->createdAt,
            'movements' => array_map(static fn (StockMovementData $movement): array => $movement->toArray(), $this->movements),
        ];
    }
}
