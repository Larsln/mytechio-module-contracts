<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a `FixedAssets` method is called with an unknown asset ID.
 */
final class FixedAssetNotFoundException extends ContractException
{
    public function __construct(
        public readonly int $id,
    ) {
        parent::__construct(sprintf('Anlagegut mit ID %d wurde nicht gefunden.', $id));
    }
}
