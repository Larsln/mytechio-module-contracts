<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\FixedAssets;

/**
 * Depreciation kind of a fixed asset.
 */
enum FixedAssetKind: string
{
    /** Straight-line depreciation over the useful life, pro rata to the month. */
    case Depreciable = 'depreciable';

    /** Declining-balance depreciation (at most 30 % p.a.), switching to straight-line when that is more favourable. */
    case Degressive = 'degressive';

    /** Low-value asset: written off in full in the month of acquisition. */
    case LowValue = 'low_value';

    /** Pooled asset: written down in five equal yearly parts, without monthly pro rata. */
    case Pool = 'pool';

    /** Not depreciable (e.g. land). */
    case NonDepreciable = 'non_depreciable';

    /** Inventory list only: tracked, but never capitalized, depreciated or posted. */
    case ListOnly = 'list_only';

    /** Whether the kind has any effect on the books (everything except `ListOnly`). */
    public function hasAccountingEffect(): bool
    {
        return $this !== self::ListOnly;
    }

    /** Whether a useful life in years is required (`Depreciable`, `Degressive`). */
    public function requiresUsefulLife(): bool
    {
        return $this === self::Depreciable || $this === self::Degressive;
    }
}
