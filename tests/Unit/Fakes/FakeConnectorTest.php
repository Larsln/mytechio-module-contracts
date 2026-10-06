<?php

declare(strict_types=1);

use MyTechIO\Contracts\Connectors\ConnectorAction;
use MyTechIO\Contracts\Connectors\ConnectorStatus;
use MyTechIO\Contracts\Connectors\SyncReport;
use MyTechIO\Contracts\Fakes\FakeConnector;

it('defaults to a fake key, domain asset type and an empty sync report', function () {
    $connector = new FakeConnector;

    expect($connector->connectorKey())->toBe('fake')
        ->and($connector->assetTypes())->toBe(['domain'])
        ->and($connector->sync())->toBeInstanceOf(SyncReport::class)
        ->and($connector->sync()->created)->toBe(0)
        ->and($connector->actions())->toBe([])
        ->and($connector->events())->toBe([]);
});

it('allows configuring key and asset types via the constructor', function () {
    $connector = new FakeConnector(key: 'domainrobot', types: ['domain', 'certificate']);

    expect($connector->connectorKey())->toBe('domainrobot')
        ->and($connector->assetTypes())->toBe(['domain', 'certificate']);
});

it('returns a seeded status for a known external id', function () {
    $connector = new FakeConnector;
    $connector->seedStatus(new ConnectorStatus(
        externalId: 'example.de',
        status: 'active',
        statusLabel: 'Aktiv',
        renewsAt: '2027-01-01',
        autorenew: true,
        locked: false,
        url: null,
    ));

    expect($connector->status('example.de')?->status)->toBe('active')
        ->and($connector->status('unknown.de'))->toBeNull();
});

it('allows configuring sync report, actions and events', function () {
    $connector = new FakeConnector;

    $connector->withSyncReport(new SyncReport(created: 1, updated: 2, removed: 0));
    $connector->withActions([new ConnectorAction(key: 'autorenew_on', label: 'Autorenew aktivieren')]);
    $connector->withEvents(['App\\Events\\Domain\\DomainsSynced']);

    expect($connector->sync()->created)->toBe(1)
        ->and($connector->actions())->toHaveCount(1)
        ->and($connector->events())->toBe(['App\\Events\\Domain\\DomainsSynced']);
});
