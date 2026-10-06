<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\PeriodClosedException;
use MyTechIO\Contracts\ContractException;

it('extends ContractException and carries the closed date', function () {
    $exception = new PeriodClosedException(bookedOn: '2026-09-30');

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->bookedOn)->toBe('2026-09-30')
        ->and($exception->getMessage())->toContain('2026-09-30');
});
