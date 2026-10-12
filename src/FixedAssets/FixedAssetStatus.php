<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * Register status of a fixed asset.
 */
enum FixedAssetStatus: string
{
    case Active = 'active';
    case Disposed = 'disposed';
}
