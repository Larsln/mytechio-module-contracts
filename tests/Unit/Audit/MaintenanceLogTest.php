<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Audit\MaintenanceLog;
use MyTechIO\Contracts\Fakes\FakeMaintenanceLog;

it('keeps the appended entries in order', function () {
    $log = new FakeMaintenanceLog;
    $actor = new ActorRef(1, 'Admin');

    $log->record($actor, 'stock.negative_override', 'App\\Models\\Article', 7, ['quantity' => '3.0000']);
    $log->record($actor, 'other.action');

    expect($log)->toBeInstanceOf(MaintenanceLog::class)
        ->and($log->entries())->toHaveCount(2)
        ->and($log->entries()[0]['action'])->toBe('stock.negative_override')
        ->and($log->entries()[0]['subject_id'])->toBe(7)
        ->and($log->entries()[0]['context'])->toBe(['quantity' => '3.0000'])
        ->and($log->entries()[1]['subject_type'])->toBeNull();
});
