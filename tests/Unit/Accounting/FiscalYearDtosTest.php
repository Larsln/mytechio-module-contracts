<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\FiscalPeriodData;
use MyTechIO\Contracts\Accounting\FiscalYearBoundaries;
use MyTechIO\Contracts\Accounting\FiscalYearData;
use MyTechIO\Contracts\Accounting\FiscalYearStatus;
use MyTechIO\Contracts\Accounting\FiscalYearStatusData;

it('converts an open year to an array', function () {
    $year = new FiscalYearData(2026, '2026-01-01', '2026-12-31', FiscalYearStatus::Open, 3);

    expect($year->toArray())->toBe([
        'year' => 2026,
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-12-31',
        'status' => 'open',
        'closed_periods' => 3,
        'is_closable' => false,
        'result_amount' => null,
        'closed_at' => null,
        'closed_by_label' => null,
        'opening_entry_number' => null,
    ]);
});

it('converts a closed year with its result to an array', function () {
    $year = new FiscalYearData(2025, '2025-01-01', '2025-12-31', FiscalYearStatus::Closed, 13, false, '-1200.5000', '2026-01-20T10:00:00+00:00', 'Lars', 'B-2026-00001');

    expect($year->toArray())->toMatchArray(['status' => 'closed', 'result_amount' => '-1200.5000', 'opening_entry_number' => 'B-2026-00001']);
});

it('converts boundaries, periods and the status to arrays', function () {
    $period = new FiscalPeriodData(1, '2026-01-01', '2026-01-31', FiscalYearStatus::Closed, '2026-02-02T08:00:00+00:00', 'Lars');
    $status = new FiscalYearStatusData(2026, FiscalYearStatus::Open, [$period]);

    expect((new FiscalYearBoundaries(2026, '2026-01-01', '2026-12-31'))->toArray())->toBe(['year' => 2026, 'starts_on' => '2026-01-01', 'ends_on' => '2026-12-31'])
        ->and($status->toArray()['periods'][0])->toBe([
            'period_no' => 1,
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-01-31',
            'status' => 'closed',
            'closed_at' => '2026-02-02T08:00:00+00:00',
            'closed_by_label' => 'Lars',
        ]);
});
