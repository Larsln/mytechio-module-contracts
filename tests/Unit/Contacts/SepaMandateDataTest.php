<?php

declare(strict_types=1);

use MyTechIO\Contracts\Contacts\NewSepaMandate;
use MyTechIO\Contracts\Contacts\SepaMandateData;

it('converts a mandate to an array with snake_case keys', function () {
    $mandate = new SepaMandateData(1, 5, 'M-1', 'core', 'active', '2026-10-01', null, null, '**** 3000', 'stripe', 'sepa_1');

    expect($mandate->toArray())->toBe([
        'id' => 1,
        'contact_id' => 5,
        'reference' => 'M-1',
        'type' => 'core',
        'status' => 'active',
        'signed_at' => '2026-10-01',
        'revoked_at' => null,
        'revocation_reason' => null,
        'iban_masked' => '**** 3000',
        'source' => 'stripe',
        'external_id' => 'sepa_1',
    ]);
});

it('defaults a new mandate to type core without provider data', function () {
    $mandate = new NewSepaMandate(contactId: 5, reference: 'M-1', signedAt: '2026-10-01');

    expect($mandate->type)->toBe('core')
        ->and($mandate->toArray())->toMatchArray(['source' => null, 'external_id' => null, 'iban' => null, 'iban_last4' => null, 'bank_name' => null]);
});
