<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The closing of the books: the layouts of the closing statements, the bookings
 * of the year-end close and the fiscal-year rules. The core builds the
 * statements from the journal balances as the layouts say and books the close;
 * the layouts are data, not code.
 */
interface ClosingProfile
{
    /**
     * The statements the profile lays out (`StatementLayout::BALANCE_SHEET`, `StatementLayout::PROFIT_AND_LOSS`).
     *
     * @return list<StatementLayout>
     */
    public function statements(): array;

    /**
     * The bookings of the year-end close.
     *
     * @return list<ClosingRule>
     */
    public function closingEntries(): array;

    public function fiscalYearRules(): FiscalYearRules;
}
