<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

/**
 * Kinds of stock vouchers (Lagerbelege): a write-off, a stocktake that sets the
 * stock to the counted value, or a hand-entered movement.
 */
enum StockVoucherType: string
{
    case WriteOff = 'write_off';
    case InventoryCount = 'inventory_count';
    case Manual = 'manual';
}
