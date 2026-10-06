<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

/**
 * Quelle von Objekt-Vorschlägen für den künftigen Objekt-Picker
 * (Zielbild Phase 5) — Module, die externe Objekte verwalten (z. B.
 * Domains bei einem Registrar), bieten darüber eine Suche an, ohne dass
 * der Kern diese Objekte bereits als Kundenobjekt angelegt haben muss.
 */
interface AssetSource
{
    /**
     * Eindeutiger Schlüssel des Moduls, z. B. `"domainrobot"`.
     */
    public function sourceKey(): string;

    /**
     * @return list<string> Von dieser Quelle angebotene Kundenobjekt-Typen, z. B. `["domain"]`.
     */
    public function assetTypes(): array;

    /**
     * @return list<AssetSuggestion>
     */
    public function suggest(string $type, string $query, int $limit = 20): array;
}
