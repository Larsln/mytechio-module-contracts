<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeUnits;
use MyTechIO\Contracts\Units\UnitData;

function makeUnit(string $code, string $name, bool $isDefault = false): UnitData
{
    return new UnitData($code, $name, null, null, $isDefault);
}

it('returns seeded units in insertion order', function () {
    $units = new FakeUnits;
    $units->seed(makeUnit('Stk', 'Stück'));
    $units->seed(makeUnit('Std', 'Stunde'));
    $units->seed(makeUnit('kg', 'Kilogramm'));

    expect(array_map(fn (UnitData $u) => $u->code, $units->all()))->toBe(['Stk', 'Std', 'kg']);
});

it('finds a unit by its exact code', function () {
    $units = new FakeUnits;
    $units->seed(makeUnit('Stk', 'Stück'));

    expect($units->find('Stk')?->name)->toBe('Stück')
        ->and($units->find('stk'))->toBeNull()
        ->and($units->find('m'))->toBeNull();
});

it('keeps a single default unit', function () {
    $units = new FakeUnits;
    $units->seed(makeUnit('Stk', 'Stück', isDefault: true));
    $units->seed(makeUnit('Std', 'Stunde'));

    expect($units->default()?->code)->toBe('Stk');

    $units->seed(makeUnit('Std', 'Stunde', isDefault: true));

    expect($units->default()?->code)->toBe('Std')
        ->and($units->find('Stk')?->isDefault)->toBeFalse();
});

it('has no default while none is marked', function () {
    $units = new FakeUnits;
    $units->seed(makeUnit('Stk', 'Stück'));

    expect($units->default())->toBeNull();
});
