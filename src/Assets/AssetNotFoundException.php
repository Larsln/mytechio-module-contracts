<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when `CustomerAssets::cancel()` is called with an unknown ID.
 */
final class AssetNotFoundException extends ContractException
{
    public function __construct(
        public readonly int $id,
    ) {
        parent::__construct(sprintf('Kundenobjekt mit ID %d wurde nicht gefunden.', $id));
    }
}
