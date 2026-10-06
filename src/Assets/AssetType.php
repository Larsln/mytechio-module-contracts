<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

/**
 * Bekannte Kundenobjekt-Typen.
 *
 * Reine Konstanten statt Enum, weil Module eigene Typen einführen dürfen
 * (z. B. künftig `mailbox`) — die Liste hier ist nicht abschließend,
 * sondern dokumentiert die heute vom Kern geführten Typen.
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
        // Reine Konstanten-Klasse, nicht instanziierbar.
    }
}
