<?php

declare(strict_types=1);

use MyTechIO\Contracts\Modules\HealthStatus;

it('builds an ok status via the static constructor', function () {
    $status = HealthStatus::ok('12 Domains, letzter Sync vor 3 Minuten.', ['domains' => 12]);

    expect($status->state)->toBe(HealthStatus::OK)
        ->and($status->summary)->toBe('12 Domains, letzter Sync vor 3 Minuten.')
        ->and($status->details)->toBe(['domains' => 12]);
});

it('builds warning, error and unknown statuses via the static constructors', function () {
    expect(HealthStatus::warning('Zugangsdaten fehlen.')->state)->toBe(HealthStatus::WARNING)
        ->and(HealthStatus::error('Verbindung fehlgeschlagen.')->state)->toBe(HealthStatus::ERROR)
        ->and(HealthStatus::unknown('Noch kein Sync gelaufen.')->state)->toBe(HealthStatus::UNKNOWN);
});

it('defaults details to an empty array', function () {
    expect(HealthStatus::ok('OK.')->details)->toBe([]);
});

it('converts to an array with snake_case keys', function () {
    $status = new HealthStatus(state: HealthStatus::WARNING, summary: 'Zugangsdaten fehlen.', details: ['configured' => false]);

    expect($status->toArray())->toBe([
        'state' => 'warning',
        'summary' => 'Zugangsdaten fehlen.',
        'details' => ['configured' => false],
    ]);
});
