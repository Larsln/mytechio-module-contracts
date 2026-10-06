<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;

it('extends RuntimeException', function () {
    $exception = new ContractException('irgendein Vertragsfehler');

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getMessage())->toBe('irgendein Vertragsfehler');
});
