<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\PriceForm;
use MyTechIO\Contracts\Articles\PriceOptionData;
use MyTechIO\Contracts\ContractException;

it('converts to an array with snake_case keys and enum values', function () {
    $option = new PriceOptionData(5, 'jährlich', PriceForm::PerPeriod, 'year', 1, '135.0000', '110.0000', false);

    expect($option->toArray())->toBe([
        'id' => 5,
        'label' => 'jährlich',
        'price_form' => 'per_period',
        'interval_unit' => 'year',
        'interval_count' => 1,
        'price_b2c_net' => '135.0000',
        'price_b2b_net' => '110.0000',
        'is_default' => false,
    ]);
});

it('accepts a one-off option without an interval', function () {
    $option = new PriceOptionData(1, 'Einzelpreis', PriceForm::OneOff, null, null, '10', '9', true);

    expect($option->intervalUnit)->toBeNull()
        ->and($option->priceForm->label())->toBe('Einmalig');
});

it('rejects an invalid combination of form and interval', function (PriceForm $form, ?string $unit, ?int $count, string $price) {
    new PriceOptionData(1, 'x', $form, $unit, $count, $price, '1', true);
})->with([
    'one-off with interval' => [PriceForm::OneOff, 'month', 1, '1'],
    'per period without interval' => [PriceForm::PerPeriod, null, null, '1'],
    'per period with unknown unit' => [PriceForm::PerPeriod, 'decade', 1, '1'],
    'per period with zero count' => [PriceForm::PerPeriod, 'month', 0, '1'],
    'malformed price' => [PriceForm::OneOff, null, null, '1,5'],
])->throws(ContractException::class);
