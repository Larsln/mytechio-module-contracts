<?php

declare(strict_types=1);

use MyTechIO\Contracts\Banking\StatementContext;

it('carries IBAN, currency and options', function () {
    $context = new StatementContext('DE02120300000000202051', 'EUR', ['csv_profile' => 'generic']);

    expect($context->toArray())->toBe([
        'account_iban' => 'DE02120300000000202051',
        'currency' => 'EUR',
        'options' => ['csv_profile' => 'generic'],
    ]);
});

it('allows accounts without an IBAN and defaults the options to empty', function () {
    $context = new StatementContext(null, 'EUR');

    expect($context->accountIban)->toBeNull()
        ->and($context->options)->toBe([]);
});
