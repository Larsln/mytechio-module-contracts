<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a `Stock` method is called with an unknown voucher number.
 */
final class StockVoucherNotFoundException extends ContractException
{
    public function __construct(
        public readonly string $number,
    ) {
        parent::__construct(sprintf('Lagerbeleg %s wurde nicht gefunden.', $number));
    }
}
