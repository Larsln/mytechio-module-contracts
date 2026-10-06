<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\ExtractionContextData;

it('exposes expense accounts and units as arrays', function () {
    $context = new ExtractionContextData(
        expenseAccounts: [
            ['number' => '6815', 'name' => 'Bürobedarf', 'category' => null],
        ],
        units: ['Stk', 'h'],
    );

    expect($context->toArray())->toBe([
        'expense_accounts' => [
            ['number' => '6815', 'name' => 'Bürobedarf', 'category' => null],
        ],
        'units' => ['Stk', 'h'],
    ]);
});
