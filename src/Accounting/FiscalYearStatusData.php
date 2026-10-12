<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * The status of a fiscal year together with the lock state of its 13 periods.
 */
final readonly class FiscalYearStatusData
{
    /**
     * @param  list<FiscalPeriodData>  $periods  Periods 1 to 13 in order.
     */
    public function __construct(
        public int $year,
        public FiscalYearStatus $status,
        public array $periods,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'status' => $this->status->value,
            'periods' => array_map(static fn (FiscalPeriodData $period): array => $period->toArray(), $this->periods),
        ];
    }
}
