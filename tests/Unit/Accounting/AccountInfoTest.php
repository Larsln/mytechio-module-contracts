<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\AccountInfo;

it('converts to an array', function () {
    $account = new AccountInfo(number: '1200', name: 'Bank', type: 'asset');

    expect($account->toArray())->toBe([
        'number' => '1200',
        'name' => 'Bank',
        'type' => 'asset',
        'id' => null,
        'is_active' => true,
        'is_core' => false,
        'name_long' => null,
        'category' => null,
        'parent_id' => null,
        'default_tax_category_id' => null,
        'tax_scenario' => null,
        'is_commonly_used' => true,
    ]);
});

it('carries the master data of a core account', function () {
    $account = new AccountInfo('0650', 'Büroeinrichtung', 'asset', 17, false, true, 'Büroeinrichtung lang', 'Sachanlagen', 3, 4, 'standard', false);

    expect($account->toArray())->toMatchArray([
        'id' => 17,
        'is_active' => false,
        'is_core' => true,
        'name_long' => 'Büroeinrichtung lang',
        'category' => 'Sachanlagen',
        'parent_id' => 3,
        'default_tax_category_id' => 4,
        'tax_scenario' => 'standard',
        'is_commonly_used' => false,
    ]);
});
