<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * How a price option is charged: once per sale, or once per billing period.
 */
enum PriceForm: string
{
    case OneOff = 'one_off';
    case PerPeriod = 'per_period';

    public function label(): string
    {
        return match ($this) {
            self::OneOff => 'Einmalig',
            self::PerPeriod => 'Je Zeitraum',
        };
    }
}
