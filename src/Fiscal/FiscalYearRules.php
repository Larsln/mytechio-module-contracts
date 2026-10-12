<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The fiscal year the profile allows: the month it starts in by default and the
 * months a deviating fiscal year may start in. The fiscal year is labelled by
 * the calendar year it starts in.
 */
final readonly class FiscalYearRules
{
    /**
     * @param  list<int>  $allowedStartMonths
     */
    public function __construct(
        public int $defaultStartMonth,
        public array $allowedStartMonths,
    ) {}

    public function allows(int $startMonth): bool
    {
        return in_array($startMonth, $this->allowedStartMonths, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['default_start_month' => $this->defaultStartMonth, 'allowed_start_months' => $this->allowedStartMonths];
    }
}
