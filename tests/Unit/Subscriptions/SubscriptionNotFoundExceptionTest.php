<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Subscriptions\SubscriptionNotFoundException;

it('extends ContractException and carries the missing id', function () {
    $exception = new SubscriptionNotFoundException(42);

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->id)->toBe(42)
        ->and($exception->getMessage())->toBe('Leistung mit ID 42 wurde nicht gefunden.');
});
