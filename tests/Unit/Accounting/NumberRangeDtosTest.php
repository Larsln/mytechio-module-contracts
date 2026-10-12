<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\NumberRangeData;
use MyTechIO\Contracts\Accounting\NumberRangeNotFoundException;
use MyTechIO\Contracts\Accounting\NumberRangeSettings;
use MyTechIO\Contracts\Accounting\NumberRangeVersion;
use MyTechIO\Contracts\ContractException;

it('converts a range with its scheduled versions to an array', function () {
    $range = new NumberRangeData(
        type: 'invoice',
        label: 'Rechnung',
        prefix: 'RE-',
        padding: 5,
        includeYear: true,
        nextNumber: 12,
        preview: 'RE-2026-00012',
        isLocked: false,
        isUsed: true,
        inCollisionGroup: true,
        pending: [new NumberRangeVersion(2027, 'R-', 6, false, 'R-000001')],
    );

    expect($range->toArray())->toBe([
        'type' => 'invoice',
        'label' => 'Rechnung',
        'prefix' => 'RE-',
        'padding' => 5,
        'include_year' => true,
        'next_number' => 12,
        'preview' => 'RE-2026-00012',
        'is_locked' => false,
        'is_used' => true,
        'in_collision_group' => true,
        'pending' => [[
            'valid_from_year' => 2027,
            'prefix' => 'R-',
            'padding' => 6,
            'include_year' => false,
            'preview' => 'R-000001',
        ]],
    ]);
});

it('converts settings to an array', function () {
    expect((new NumberRangeSettings(2027, 'INV-', 4, true))->toArray())->toBe([
        'valid_from_year' => 2027,
        'prefix' => 'INV-',
        'padding' => 4,
        'include_year' => true,
    ]);
});

it('names the unknown range in a German message', function () {
    $exception = new NumberRangeNotFoundException('nope');

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->type)->toBe('nope')
        ->and($exception->getMessage())->toBe('Nummernkreis „nope“ wurde nicht gefunden.');
});
