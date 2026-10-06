<?php

declare(strict_types=1);

use MyTechIO\Contracts\Tax\VatIdCheckResult;

it('converts to an array with nullable name and address', function () {
    $result = new VatIdCheckResult(
        valid: true,
        name: 'ACME GmbH',
        address: 'Musterstraße 1, 12345 Berlin',
        requestIdentifier: 'DE-20261007-1234',
        checkedAt: '2026-10-07T10:00:00+02:00',
    );

    expect($result->toArray())->toBe([
        'valid' => true,
        'name' => 'ACME GmbH',
        'address' => 'Musterstraße 1, 12345 Berlin',
        'request_identifier' => 'DE-20261007-1234',
        'checked_at' => '2026-10-07T10:00:00+02:00',
    ]);
});

it('allows a null name and address for simple confirmation', function () {
    $result = new VatIdCheckResult(
        valid: true,
        name: null,
        address: null,
        requestIdentifier: 'DE-20261007-1235',
        checkedAt: '2026-10-07T10:05:00+02:00',
    );

    expect($result->name)->toBeNull()
        ->and($result->address)->toBeNull();
});
