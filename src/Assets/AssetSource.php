<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

use MyTechIO\Contracts\Accounting\ActorRef;

/**
 * Source of asset suggestions for the upcoming asset picker (Phase 5
 * target design) — modules that manage external assets (e.g. domains at
 * a registrar) offer a search through this interface, without the core
 * needing to have already created these assets as customer assets
 * (Kundenobjekt).
 *
 * Action methods (`setAutorenew()`, `transferToCompany()`, `releaseTransfer()`)
 * are only called for keys listed in `capabilities()`. Sources that support no
 * actions `use AssetSourceDefaults`, whose implementations throw a
 * `ContractException`.
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

    /**
     * Actions this source can perform: any of `"autorenew"`, `"owner_change"`,
     * `"transfer_out"`. The caller offers only the actions listed here.
     *
     * @return list<string>
     */
    public function capabilities(): array;

    /**
     * Switches the source's automatic renewal on or off (capability
     * `"autorenew"`).
     */
    public function setAutorenew(string $externalId, bool $enabled, ActorRef $actor): AssetActionRef;

    /**
     * Changes the owner of the asset at the source to the operating company
     * (capability `"owner_change"`).
     */
    public function transferToCompany(string $externalId, ActorRef $actor): AssetActionRef;

    /**
     * Releases the asset for transfer-out, e.g. by creating an auth code
     * (capability `"transfer_out"`).
     */
    public function releaseTransfer(string $externalId, ActorRef $actor): AssetActionRef;
}
