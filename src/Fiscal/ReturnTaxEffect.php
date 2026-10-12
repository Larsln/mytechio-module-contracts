<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * How the tax amounts of a return section enter the amount payable: `Payable`
 * adds to it (owed to the tax office), `Deductible` reduces it (input VAT),
 * `None` has no tax effect (tax-exempt bases).
 */
enum ReturnTaxEffect: string
{
    case Payable = 'payable';

    case Deductible = 'deductible';

    case None = 'none';
}
