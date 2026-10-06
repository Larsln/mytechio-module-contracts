<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\AccountInfo;

it('converts to an array', function () {
    $account = new AccountInfo(number: '1200', name: 'Bank', type: 'asset');

    expect($account->toArray())->toBe([
        'number' => '1200',
        'name' => 'Bank',
        'type' => 'asset',
    ]);
});
