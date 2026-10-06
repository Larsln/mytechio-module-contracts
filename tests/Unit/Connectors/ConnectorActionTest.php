<?php

declare(strict_types=1);

use MyTechIO\Contracts\Connectors\ConnectorAction;

it('defaults permission to null and destructive to false', function () {
    $action = new ConnectorAction(key: 'autorenew_on', label: 'Autorenew aktivieren');

    expect($action->permission)->toBeNull()
        ->and($action->destructive)->toBeFalse();
});

it('converts to an array', function () {
    $action = new ConnectorAction(key: 'close', label: 'Domain löschen', permission: 'domainrobot.destroy', destructive: true);

    expect($action->toArray())->toBe([
        'key' => 'close',
        'label' => 'Domain löschen',
        'permission' => 'domainrobot.destroy',
        'destructive' => true,
    ]);
});
