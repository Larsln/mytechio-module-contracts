<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Connectors;

/**
 * Objekt-Connector für externe Registrare/Lizenzserver.
 *
 * Der Kern sammelt Implementierungen über Container-Tags (`connectors()`,
 * `forAssetType()`, siehe `ConnectorRegistry`) und bietet darüber einen
 * einheitlichen Satz an Aktionen und Status-Abfragen für Kundenobjekte an,
 * ohne die jeweilige externe API selbst zu kennen. `sync()` darf lange
 * laufen (vollständiger Abgleich) und gehört in eine Queue, niemals in
 * einen synchronen Request.
 */
interface Connector
{
    /**
     * Eindeutiger Schlüssel des Moduls, z. B. `"domainrobot"`.
     */
    public function connectorKey(): string;

    /**
     * @return list<string>
     */
    public function assetTypes(): array;

    /**
     * Vollständiger Abgleich mit der externen Quelle.
     */
    public function sync(): SyncReport;

    public function status(string $externalId): ?ConnectorStatus;

    /**
     * @return list<ConnectorAction> Aktionen, die dieser Connector für ein Objekt anbietet.
     */
    public function actions(): array;

    /**
     * @return list<string> Ereignisnamen, die der Connector dispatcht (FQCN).
     */
    public function events(): array;
}
