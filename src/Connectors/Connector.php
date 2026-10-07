<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Connectors;

/**
 * Asset connector for external registrars/license servers.
 *
 * The core collects implementations via container tags (`connectors()`,
 * `forAssetType()`, see `ConnectorRegistry`) and exposes through them a
 * unified set of actions and status queries for customer assets
 * (Kundenobjekte), without knowing the respective external API itself.
 * `sync()` may run for a long time (a full reconciliation) and belongs
 * in a queue, never in a synchronous request.
 */
interface Connector
{
    /**
     * Unique key of the module, e.g. `"domainrobot"`.
     */
    public function connectorKey(): string;

    /**
     * @return list<string>
     */
    public function assetTypes(): array;

    /**
     * Full reconciliation with the external source.
     */
    public function sync(): SyncReport;

    public function status(string $externalId): ?ConnectorStatus;

    /**
     * @return list<ConnectorAction> Actions that this connector offers for an asset.
     */
    public function actions(): array;

    /**
     * @return list<string> Event names that the connector dispatches (FQCN).
     */
    public function events(): array;
}
