<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Open or closed — used for a fiscal year (closed = year-end close done) and
 * for one of its periods (closed = locked, no further bookings).
 */
enum FiscalYearStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
}
