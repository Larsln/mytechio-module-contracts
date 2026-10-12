<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A block of ledger account numbers the profile's chart reserves for a purpose.
 */
enum AccountRangeKind: string
{
    /** Ledger accounts of bank accounts. */
    case BankLedger = 'bank_ledger';

    /** Ledger accounts of cash registers. */
    case CashLedger = 'cash_ledger';

    /** Every account a bank account or cash register may be linked to (bank, cash). */
    case LiquidAssets = 'liquid_assets';

    /** Fixed assets (capitalised purchases). */
    case FixedAssets = 'fixed_assets';
}
