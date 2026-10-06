<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Tax\InvalidVatIdFormatException;

it('extends ContractException and carries country code and vat number', function () {
    $exception = new InvalidVatIdFormatException(countryCode: 'DE', vatNumber: '123');

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->countryCode)->toBe('DE')
        ->and($exception->vatNumber)->toBe('123')
        ->and($exception->getMessage())->toContain('DE123');
});
