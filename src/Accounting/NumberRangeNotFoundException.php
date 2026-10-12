<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a `NumberRanges` method is called with an unknown range type.
 */
final class NumberRangeNotFoundException extends ContractException
{
    public function __construct(
        public readonly string $type,
    ) {
        parent::__construct(sprintf('Nummernkreis „%s“ wurde nicht gefunden.', $type));
    }
}
