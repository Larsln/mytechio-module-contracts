<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeHealthCheck;
use MyTechIO\Contracts\Modules\HealthStatus;

it('defaults to an ok status', function () {
    $healthCheck = new FakeHealthCheck;

    expect($healthCheck->health()->state)->toBe(HealthStatus::OK);
});

it('allows configuring the status via the constructor', function () {
    $status = HealthStatus::error('Verbindung fehlgeschlagen.');
    $healthCheck = new FakeHealthCheck($status);

    expect($healthCheck->health())->toBe($status);
});

it('allows reconfiguring the status via withStatus()', function () {
    $healthCheck = new FakeHealthCheck;

    $healthCheck->withStatus(HealthStatus::warning('Zugangsdaten fehlen.'));

    expect($healthCheck->health()->state)->toBe(HealthStatus::WARNING);
});
