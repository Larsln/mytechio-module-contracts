<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

use MyTechIO\Contracts\ContractException;

/**
 * The account map has no block of account numbers for a kind of range.
 */
final class AccountRangeNotMappedException extends ContractException
{
    public function __construct(
        public readonly AccountRangeKind $kind,
    ) {
        parent::__construct(sprintf('Kein Kontenbereich festgelegt für „%s“.', $kind->value));
    }
}
