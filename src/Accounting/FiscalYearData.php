<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * A fiscal year. `year` is the calendar year the fiscal year STARTS in (with a
 * start month of July, 2026-06-30 belongs to fiscal year 2025); `startsOn`
 * and `endsOn` are ISO dates. `closedPeriods` counts the locked periods of
 * the 13 (12 months plus the closing period), `isClosable` says whether the
 * year-end close would be accepted now (all 13 locked, every earlier year
 * with bookings closed). `resultAmount` (4 decimal places, negative for a
 * deficit), `closedAt` (ISO-8601), `closedByLabel` and `openingEntryNumber`
 * (the carry-forward entry) are only set on a closed year.
 */
final readonly class FiscalYearData
{
    public function __construct(
        public int $year,
        public string $startsOn,
        public string $endsOn,
        public FiscalYearStatus $status,
        public int $closedPeriods = 0,
        public bool $isClosable = false,
        public ?string $resultAmount = null,
        public ?string $closedAt = null,
        public ?string $closedByLabel = null,
        public ?string $openingEntryNumber = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'starts_on' => $this->startsOn,
            'ends_on' => $this->endsOn,
            'status' => $this->status->value,
            'closed_periods' => $this->closedPeriods,
            'is_closable' => $this->isClosable,
            'result_amount' => $this->resultAmount,
            'closed_at' => $this->closedAt,
            'closed_by_label' => $this->closedByLabel,
            'opening_entry_number' => $this->openingEntryNumber,
        ];
    }
}
