<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * One period of a fiscal year: periods 1 to 12 are the calendar months, period
 * 13 is the closing period (a single day, the fiscal year's last day).
 * A period without a lock is open.
 */
final readonly class FiscalPeriodData
{
    public function __construct(
        public int $periodNo,
        public string $startsOn,
        public string $endsOn,
        public FiscalYearStatus $status,
        public ?string $closedAt = null,
        public ?string $closedByLabel = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'period_no' => $this->periodNo,
            'starts_on' => $this->startsOn,
            'ends_on' => $this->endsOn,
            'status' => $this->status->value,
            'closed_at' => $this->closedAt,
            'closed_by_label' => $this->closedByLabel,
        ];
    }
}
