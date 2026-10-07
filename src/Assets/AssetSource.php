<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

/**
 * Source of asset suggestions for the upcoming asset picker (Phase 5
 * target design) — modules that manage external assets (e.g. domains at
 * a registrar) offer a search through this interface, without the core
 * needing to have already created these assets as customer assets
 * (Kundenobjekt).
 */
interface AssetSource
{
    /**
     * Unique key of the module, e.g. `"domainrobot"`.
     */
    public function sourceKey(): string;

    /**
     * @return list<string> Customer asset types offered by this source, e.g. `["domain"]`.
     */
    public function assetTypes(): array;

    /**
     * @return list<AssetSuggestion>
     */
    public function suggest(string $type, string $query, int $limit = 20): array;
}
