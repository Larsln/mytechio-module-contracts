<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\Side;

it('is a string-backed enum with debit and credit', function () {
    expect(Side::Debit->value)->toBe('debit')
        ->and(Side::Credit->value)->toBe('credit')
        ->and(Side::from('debit'))->toBe(Side::Debit)
        ->and(Side::from('credit'))->toBe(Side::Credit);
});
