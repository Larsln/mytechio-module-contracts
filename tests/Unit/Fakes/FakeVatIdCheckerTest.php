<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeVatIdChecker;
use MyTechIO\Contracts\Tax\VatIdCheckResult;
use MyTechIO\Contracts\Tax\VatIdCheckUnavailableException;

it('returns an invalid default result for an unseeded vat id', function () {
    $checker = new FakeVatIdChecker;

    $result = $checker->check('DE', '123456789');

    expect($result->valid)->toBeFalse()
        ->and($result->name)->toBeNull()
        ->and($checker->calls())->toHaveCount(1)
        ->and($checker->calls()[0])->toBe(['country_code' => 'DE', 'vat_number' => '123456789']);
});

it('returns the seeded result for a given vat id', function () {
    $checker = new FakeVatIdChecker;
    $checker->seedResult('DE', '123456789', new VatIdCheckResult(
        valid: true,
        name: 'ACME GmbH',
        address: 'Musterstraße 1',
        requestIdentifier: 'DE-1',
        checkedAt: '2026-10-07T10:00:00+02:00',
    ));

    $result = $checker->check('DE', '123456789');

    expect($result->valid)->toBeTrue()
        ->and($result->name)->toBe('ACME GmbH');
});

it('throws VatIdCheckUnavailableException when switched to unavailable', function () {
    $checker = (new FakeVatIdChecker)->unavailable();

    $check = fn () => $checker->check('DE', '123456789');

    expect($check)->toThrow(VatIdCheckUnavailableException::class);
});
