<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Accounting\NumberRangeData;
use MyTechIO\Contracts\Accounting\NumberRangeNotFoundException;
use MyTechIO\Contracts\Accounting\NumberRangeSettings;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeNumberRanges;

function numberRange(string $type = 'company_asset', string $prefix = 'INV-', bool $includeYear = true, bool $locked = false, bool $collision = false): NumberRangeData
{
    return new NumberRangeData($type, 'Testkreis', $prefix, 5, $includeYear, 1, $prefix.'2026-00001', $locked, false, $collision);
}

function actor(): ActorRef
{
    return new ActorRef(1, 'Tester');
}

it('hands out gapless numbers per year', function () {
    $ranges = new FakeNumberRanges;
    $ranges->seed(numberRange());

    expect($ranges->next('company_asset', 2026))->toBe('INV-2026-00001')
        ->and($ranges->next('company_asset', 2026))->toBe('INV-2026-00002')
        ->and($ranges->next('company_asset', 2027))->toBe('INV-2027-00001')
        ->and($ranges->preview('company_asset', 2026))->toBe('INV-2026-00003')
        ->and($ranges->events())->toHaveCount(3)
        ->and($ranges->events()[0])->toBe(['event' => 'NumberDrawn', 'payload' => ['type' => 'company_asset', 'year' => 2026, 'number' => 'INV-2026-00001']]);
});

it('keeps one counter for a range without the year in the number', function () {
    $ranges = new FakeNumberRanges;
    $ranges->seed(numberRange('customer', '1', includeYear: false));

    expect($ranges->next('customer', 2026))->toBe('100001')
        ->and($ranges->next('customer', 2027))->toBe('100002');
});

it('reports the range as used after a number was drawn', function () {
    $ranges = new FakeNumberRanges;
    $ranges->seed(numberRange());
    $ranges->next('company_asset', (int) date('Y'));

    expect($ranges->find('company_asset')?->isUsed)->toBeTrue()
        ->and($ranges->find('company_asset')?->nextNumber)->toBe(2)
        ->and($ranges->find('missing'))->toBeNull()
        ->and($ranges->list())->toHaveCount(1);
});

it('throws for an unknown type', function () {
    (new FakeNumberRanges)->next('nope', 2026);
})->throws(NumberRangeNotFoundException::class);

it('schedules and discards a format version', function () {
    $year = (int) date('Y') + 1;
    $ranges = new FakeNumberRanges;
    $ranges->seed(numberRange());

    $updated = $ranges->update('company_asset', new NumberRangeSettings($year, 'AV-', 4, false), actor());

    expect($updated->pending)->toHaveCount(1)
        ->and($updated->pending[0]->preview)->toBe('AV-0001');

    $ranges->discardScheduled('company_asset', $year, actor());

    expect($ranges->find('company_asset')?->pending)->toBe([])
        ->and(array_column($ranges->events(), 'event'))->toBe(['NumberRangeUpdated', 'NumberRangeUpdated']);
});

it('refuses format changes the core refuses', function (NumberRangeData $range, NumberRangeSettings $settings, string $message) {
    $ranges = new FakeNumberRanges;
    $ranges->seed($range);

    expect(fn () => $ranges->update($range->type, $settings, actor()))->toThrow(ContractException::class, $message);
})->with(function () {
    $next = (int) date('Y') + 1;

    return [
        'locked range' => [numberRange(locked: true), new NumberRangeSettings($next, 'X-', 5, true), 'dauerhaft fix'],
        'current year' => [numberRange(), new NumberRangeSettings((int) date('Y'), 'X-', 5, true), 'frühestens ab dem nächsten Jahr'],
        'collision group without prefix' => [numberRange('invoice', 'RE-', collision: true), new NumberRangeSettings($next, '', 5, true), 'Präfix ist hier Pflicht'],
        'invalid padding' => [numberRange(), new NumberRangeSettings($next, 'X-', 11, true), 'ungültig'],
    ];
});

it('only discards future versions', function () {
    $ranges = new FakeNumberRanges;
    $ranges->seed(numberRange());

    $ranges->discardScheduled('company_asset', (int) date('Y'), actor());
})->throws(ContractException::class, 'Nur geplante');
