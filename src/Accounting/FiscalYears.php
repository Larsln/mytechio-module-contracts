<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

use MyTechIO\Contracts\ContractException;

/**
 * The fiscal-year calendar of the organization: the years, their boundaries
 * (which follow the configured fiscal-year start month), the lock state of
 * their periods and the year-end close.
 *
 * A year without a close record is open. There is deliberately no reopening —
 * a closed year is immutable, corrections go through a reversing entry in an
 * open period (`Journal::reverse()`). `close()` fires `FiscalYearClosed` after
 * the commit.
 */
interface FiscalYears
{
    /**
     * The years that have bookings or a close record, plus the current one,
     * newest first.
     *
     * @return list<FiscalYearData>
     */
    public function list(): array;

    /**
     * Any year, bookings or not: a year without a close record is open.
     */
    public function find(int $year): FiscalYearData;

    /**
     * The fiscal year containing today.
     */
    public function current(): FiscalYearData;

    public function boundaries(int $year): FiscalYearBoundaries;

    /**
     * The year's status with its 13 periods and their locks.
     */
    public function status(int $year): FiscalYearStatusData;

    /**
     * Whether a booking on this ISO date would be accepted: neither its period
     * nor its fiscal year is closed.
     */
    public function isPeriodOpen(string $date): bool;

    /**
     * Closes a fiscal year permanently and books the carry-forward (balance
     * carry-over) into the following year.
     *
     * @throws ContractException with a German message if the year is already closed, a period is still open or an
     *                           earlier year with bookings is not closed yet.
     */
    public function close(int $year, ActorRef $actor): FiscalYearData;
}
