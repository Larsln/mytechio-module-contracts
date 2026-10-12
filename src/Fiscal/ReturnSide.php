<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * Which journal side counts positive when the lines of a tax code are summed:
 * revenue-side codes read as credit minus debit, input-side codes as debit minus
 * credit. A cancellation mirrors the booking and therefore nets out either way.
 */
enum ReturnSide: string
{
    case Credit = 'credit';

    case Debit = 'debit';
}
