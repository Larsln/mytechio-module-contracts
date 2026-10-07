<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

/**
 * Known customer asset (Kundenobjekt) types.
 *
 * Plain constants instead of an enum because modules are allowed to
 * introduce their own types (e.g. `mailbox` in the future) — the list
 * here is not exhaustive, it documents the types currently maintained
 * by the core.
 */
final class AssetType
{
    public const string Domain = 'domain';

    public const string License = 'license';

    public const string Hosting = 'hosting';

    public const string Certificate = 'certificate';

    public const string Other = 'other';

    private function __construct()
    {
        // Pure constants class, not instantiable.
    }
}
