<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Documents\ExtractionFailedException;

it('extends ContractException and carries a message', function () {
    $exception = new ExtractionFailedException('Die Extraktion ist fehlgeschlagen.');

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->getMessage())->toBe('Die Extraktion ist fehlgeschlagen.');
});
