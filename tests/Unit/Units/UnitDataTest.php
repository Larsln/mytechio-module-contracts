<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Units\UnitData;

it('converts to an array with snake_case keys', function () {
    $unit = new UnitData(code: 'Std', name: 'Stunde', nameEn: 'Hour', uneceCode: 'HUR', isDefault: false);

    expect($unit->toArray())->toBe([
        'code' => 'Std',
        'name' => 'Stunde',
        'name_en' => 'Hour',
        'unece_code' => 'HUR',
        'is_default' => false,
    ]);
});

it('rejects an empty code', function () {
    expect(fn () => new UnitData(code: ' ', name: 'Leer', nameEn: null, uneceCode: null, isDefault: false))
        ->toThrow(ContractException::class, 'code must not be empty');
});
