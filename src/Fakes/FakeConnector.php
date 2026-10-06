<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Connectors\Connector;
use MyTechIO\Contracts\Connectors\ConnectorAction;
use MyTechIO\Contracts\Connectors\ConnectorStatus;
use MyTechIO\Contracts\Connectors\SyncReport;

/**
 * Test-Double für `Connector`: konfigurierbarer Schlüssel/Typen, Status
 * je `externalId` per `seedStatus()`, Sync-Ergebnis per `withSyncReport()`.
 */
final class FakeConnector implements Connector
{
    /**
     * @var array<string, ConnectorStatus>
     */
    private array $statuses = [];

    /**
     * @var list<ConnectorAction>
     */
    private array $connectorActions = [];

    /**
     * @var list<string>
     */
    private array $connectorEvents = [];

    private SyncReport $syncReport;

    /**
     * @param  list<string>  $types
     */
    public function __construct(
        private readonly string $key = 'fake',
        private readonly array $types = ['domain'],
    ) {
        $this->syncReport = new SyncReport(created: 0, updated: 0, removed: 0);
    }

    public function connectorKey(): string
    {
        return $this->key;
    }

    public function assetTypes(): array
    {
        return $this->types;
    }

    public function sync(): SyncReport
    {
        return $this->syncReport;
    }

    public function status(string $externalId): ?ConnectorStatus
    {
        return $this->statuses[$externalId] ?? null;
    }

    public function actions(): array
    {
        return $this->connectorActions;
    }

    public function events(): array
    {
        return $this->connectorEvents;
    }

    public function seedStatus(ConnectorStatus $status): void
    {
        $this->statuses[$status->externalId] = $status;
    }

    public function withSyncReport(SyncReport $report): void
    {
        $this->syncReport = $report;
    }

    /**
     * @param  list<ConnectorAction>  $actions
     */
    public function withActions(array $actions): void
    {
        $this->connectorActions = $actions;
    }

    /**
     * @param  list<string>  $events
     */
    public function withEvents(array $events): void
    {
        $this->connectorEvents = $events;
    }
}
