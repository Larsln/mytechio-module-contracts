<?php

declare(strict_types=1);

use MyTechIO\Contracts\Ai\StructuredCompletionException;
use MyTechIO\Contracts\ContractException;

it('extends ContractException and carries a message', function () {
    $exception = new StructuredCompletionException('Die KI-Anfrage ist fehlgeschlagen.');

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->getMessage())->toBe('Die KI-Anfrage ist fehlgeschlagen.');
});
