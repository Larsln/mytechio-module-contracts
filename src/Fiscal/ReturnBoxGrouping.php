<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * How a return box relates to the rate keys found in the journal: `Single` is
 * one row, `PerRate` one row per rate key (with the box texts per rate from the
 * variants), `SumOverRates` one row summing every rate key.
 */
enum ReturnBoxGrouping: string
{
    case Single = 'single';

    case PerRate = 'per_rate';

    case SumOverRates = 'sum_over_rates';
}
