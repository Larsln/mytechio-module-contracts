<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Debit or credit side of a journal line.
 */
enum Side: string
{
    case Debit = 'debit';
    case Credit = 'credit';
}
