<?php

declare(strict_types=1);

use MyTechIO\Contracts\Connectors\SyncReport;

it('defaults notes to an empty array', function () {
    $report = new SyncReport(created: 1, updated: 2, removed: 0);

    expect($report->notes)->toBe([]);
});

it('converts to an array', function () {
    $report = new SyncReport(created: 1, updated: 2, removed: 0, notes: ['example.de aktualisiert']);

    expect($report->toArray())->toBe([
        'created' => 1,
        'updated' => 2,
        'removed' => 0,
        'notes' => ['example.de aktualisiert'],
    ]);
});
