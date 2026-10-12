<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

/**
 * Kinds of entries in the quantity ledger. `Incoming` and `Return` add to the
 * stock, every other kind removes from it, except `Correction` which carries
 * its own sign (stocktake differences, reversals).
 */
enum StockMovementType: string
{
    case Incoming = 'incoming';
    case OutgoingSale = 'outgoing_sale';
    case Return = 'return';
    case Damage = 'damage';
    case Loss = 'loss';
    case Correction = 'correction';
    case ConsumptionNoSale = 'consumption_no_sale';
    case FreeOfChargeTransfer = 'free_of_charge_transfer';

    public function isPositive(): bool
    {
        return $this === self::Incoming || $this === self::Return;
    }

    /**
     * The kinds `Stock::writeOff()` accepts: breakage, shrinkage, internal use
     * (no VAT) and a deemed supply for no consideration (with VAT).
     *
     * @return list<self>
     */
    public static function writeOffCases(): array
    {
        return [self::Damage, self::Loss, self::ConsumptionNoSale, self::FreeOfChargeTransfer];
    }
}
