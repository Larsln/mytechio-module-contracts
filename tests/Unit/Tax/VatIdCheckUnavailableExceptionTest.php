<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Tax\VatIdCheckUnavailableException;

it('extends ContractException with a default German message', function () {
    $exception = new VatIdCheckUnavailableException;

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->getMessage())->toBe('Die VIES-Prüfung ist derzeit nicht erreichbar.');
});

it('allows a custom reason', function () {
    $exception = new VatIdCheckUnavailableException('Zeitüberschreitung beim VIES-Dienst.');

    expect($exception->getMessage())->toBe('Zeitüberschreitung beim VIES-Dienst.');
});
