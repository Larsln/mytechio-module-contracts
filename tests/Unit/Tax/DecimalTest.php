<?php

declare(strict_types=1);

use MyTechIO\Contracts\Tax\Decimal;

it('rounds half to even at any scale', function (string $value, int $scale, string $expected) {
    expect(Decimal::round($value, $scale))->toBe($expected);
})->with([
    ['0.005', 2, '0.00'],
    ['0.015', 2, '0.02'],
    ['0.025', 2, '0.02'],
    ['0.0250001', 2, '0.03'],
    ['1.995', 2, '2.00'],
    ['999999.995', 2, '1000000.00'],
    ['12', 2, '12.00'],
    ['12.3', 4, '12.3000'],
    ['101.680672268', 4, '101.6807'],
    ['19.319333', 4, '19.3193'],
    ['0.00005', 4, '0.0000'],
    ['0.00015', 4, '0.0002'],
    ['0.00025', 4, '0.0002'],
    ['-0.015', 2, '-0.02'],
    ['-0.004', 2, '0.00'],
    ['2.5', 0, '2'],
    ['3.5', 0, '4'],
    ['.5', 0, '0'],
]);

it('calculates a document line like the core: four decimals per step, half to even', function () {
    // 121.00 gross at 19 %: unit net 101.6807.
    expect(Decimal::line('1', '101.6807', '19.00'))->toBe(['net' => '101.6807', 'tax' => '19.3193', 'gross' => '121.0000'])
        ->and(Decimal::line('3', '101.6807', '19'))->toBe(['net' => '305.0421', 'tax' => '57.9580', 'gross' => '363.0001'])
        ->and(Decimal::line('100', '4.1176', '19.00'))->toBe(['net' => '411.7600', 'tax' => '78.2344', 'gross' => '489.9944'])
        ->and(Decimal::line('7', '4.0833', '20.00'))->toBe(['net' => '28.5831', 'tax' => '5.7166', 'gross' => '34.2997'])
        ->and(Decimal::line('2.5', '10.0000', '0'))->toBe(['net' => '25.0000', 'tax' => '0.0000', 'gross' => '25.0000'])
        // A line discount comes off the net before the tax.
        ->and(Decimal::line('2', '50.0000', '19.00', '10'))->toBe(['net' => '90.0000', 'tax' => '17.1000', 'gross' => '107.1000'])
        ->and(Decimal::multiply('1.21', '100.00', 2))->toBe('121.00');
});
