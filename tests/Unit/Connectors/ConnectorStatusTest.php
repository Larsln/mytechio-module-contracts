<?php

declare(strict_types=1);

use MyTechIO\Contracts\Connectors\ConnectorStatus;

it('converts to an array with snake_case keys', function () {
    $status = new ConnectorStatus(
        externalId: 'example.de',
        status: 'active',
        statusLabel: 'Aktiv',
        renewsAt: '2027-01-01',
        autorenew: true,
        locked: false,
        url: 'https://robot.s-dns.de/domains/example.de',
        attributes: ['registrar' => 'kyberio'],
    );

    expect($status->toArray())->toBe([
        'external_id' => 'example.de',
        'status' => 'active',
        'status_label' => 'Aktiv',
        'renews_at' => '2027-01-01',
        'autorenew' => true,
        'locked' => false,
        'url' => 'https://robot.s-dns.de/domains/example.de',
        'attributes' => ['registrar' => 'kyberio'],
    ]);
});
