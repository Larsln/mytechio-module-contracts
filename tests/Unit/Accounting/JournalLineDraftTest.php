<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\JournalLineDraft;
use MyTechIO\Contracts\Accounting\Side;

it('converts to an array with the side as its string value', function () {
    $line = new JournalLineDraft(accountNumber: '1200', side: Side::Debit, amount: '119.0000', taxCode: 'DE19', memo: 'Rechnung');

    expect($line->toArray())->toBe([
        'account_number' => '1200',
        'side' => 'debit',
        'amount' => '119.0000',
        'tax_code' => 'DE19',
        'memo' => 'Rechnung',
    ]);
});
