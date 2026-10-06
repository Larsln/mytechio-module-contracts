<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Soll oder Haben einer Buchungszeile.
 */
enum Side: string
{
    case Debit = 'debit';
    case Credit = 'credit';
}
