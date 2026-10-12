<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * First and last day (ISO dates) of a fiscal year, derived from the
 * organization's fiscal-year start month.
 */
final readonly class FiscalYearBoundaries
{
    public function __construct(
        public int $year,
        public string $startsOn,
        public string $endsOn,
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
        ];
    }
}
