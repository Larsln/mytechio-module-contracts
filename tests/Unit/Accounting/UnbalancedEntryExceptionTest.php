<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\UnbalancedEntryException;
use MyTechIO\Contracts\ContractException;

it('extends ContractException and carries both totals', function () {
    $exception = new UnbalancedEntryException(debitTotal: '100.0000', creditTotal: '99.0000');

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->debitTotal)->toBe('100.0000')
        ->and($exception->creditTotal)->toBe('99.0000')
        ->and($exception->getMessage())->toContain('100.0000')
        ->and($exception->getMessage())->toContain('99.0000');
});
