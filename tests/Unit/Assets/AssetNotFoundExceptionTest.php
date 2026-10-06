<?php

declare(strict_types=1);

use MyTechIO\Contracts\Assets\AssetNotFoundException;
use MyTechIO\Contracts\ContractException;

it('extends ContractException and carries the missing id', function () {
    $exception = new AssetNotFoundException(42);

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->id)->toBe(42)
        ->and($exception->getMessage())->toContain('42');
});
