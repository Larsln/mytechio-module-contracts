<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Accounting\FiscalYearStatus;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeFiscalYears;

it('derives the boundaries from the start month', function () {
    expect((new FakeFiscalYears)->boundaries(2026)->toArray())->toBe(['year' => 2026, 'starts_on' => '2026-01-01', 'ends_on' => '2026-12-31'])
        ->and((new FakeFiscalYears(7))->boundaries(2025)->toArray())->toBe(['year' => 2025, 'starts_on' => '2025-07-01', 'ends_on' => '2026-06-30']);
});

it('reports the 13 periods with their locks', function () {
    $years = new FakeFiscalYears;
    $years->lockPeriod(2026, 1);

    $status = $years->status(2026);

    expect($status->periods)->toHaveCount(13)
        ->and($status->periods[0]->status)->toBe(FiscalYearStatus::Closed)
        ->and($status->periods[1]->status)->toBe(FiscalYearStatus::Open)
        ->and($status->periods[12]->startsOn)->toBe('2026-12-31')
        ->and($status->periods[1]->endsOn)->toBe('2026-02-28');
});

it('answers whether a date can still be booked', function () {
    $years = new FakeFiscalYears;
    $years->lockPeriod(2026, 1);

    expect($years->isPeriodOpen('2026-01-15'))->toBeFalse()
        ->and($years->isPeriodOpen('2026-02-01'))->toBeTrue();

    $shifted = new FakeFiscalYears(7);
    $shifted->lockPeriod(2025, 1);

    expect($shifted->isPeriodOpen('2025-07-31'))->toBeFalse()
        ->and($shifted->isPeriodOpen('2026-01-10'))->toBeTrue();
});

it('closes a year only when all periods are locked and earlier years are closed', function () {
    $years = new FakeFiscalYears;
    $years->lockYear(2025);
    $years->lockYear(2026);
    $actor = new ActorRef(1, 'Lars');

    expect($years->find(2026)->isClosable)->toBeFalse();
    expect(fn () => $years->close(2026, $actor))->toThrow(ContractException::class, 'frühere Geschäftsjahr 2025');

    $closed = $years->close(2025, $actor);

    expect($closed->status)->toBe(FiscalYearStatus::Closed)
        ->and($closed->closedByLabel)->toBe('Lars')
        ->and($years->isPeriodOpen('2025-06-01'))->toBeFalse()
        ->and($years->find(2026)->isClosable)->toBeTrue()
        ->and($years->events()[0]['event'])->toBe('FiscalYearClosed');

    expect(fn () => $years->close(2025, $actor))->toThrow(ContractException::class, 'bereits abgeschlossen');
});

it('lists booked, closed and the current year newest first', function () {
    $years = new FakeFiscalYears;
    $years->seedBookedYear(2024);

    $list = array_map(fn ($year) => $year->year, $years->list());

    expect($list[0])->toBe((int) date('Y'))
        ->and(end($list))->toBe(2024)
        ->and($years->current()->year)->toBe((int) date('Y'));
});

it('refuses to close a year with an open period', function () {
    $years = new FakeFiscalYears;
    $years->lockPeriod(2026, 1);

    $years->close(2026, new ActorRef(null, 'Job'));
})->throws(ContractException::class, 'noch offen');
