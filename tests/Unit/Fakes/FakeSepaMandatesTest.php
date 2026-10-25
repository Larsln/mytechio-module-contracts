<?php

declare(strict_types=1);

use MyTechIO\Contracts\Contacts\NewSepaMandate;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeSepaMandates;

it('creates active mandates with ascending ids and a masked IBAN', function () {
    $mandates = new FakeSepaMandates;

    $full = $mandates->create(new NewSepaMandate(contactId: 1, reference: 'M-1', signedAt: '2026-10-01', iban: 'DE89 3704 0044 0532 0130 00'));
    $last4 = $mandates->create(new NewSepaMandate(contactId: 1, reference: 'M-2', signedAt: '2026-10-02', source: 'stripe', externalId: 'sepa_1', ibanLast4: '3000'));

    expect($full->id)->toBe(1)
        ->and($full->status)->toBe('active')
        ->and($full->type)->toBe('core')
        ->and($full->ibanMasked)->toBe('DE89 **** **** **** **30 00')
        ->and($last4->id)->toBe(2)
        ->and($last4->ibanMasked)->toBe('**** 3000')
        ->and($last4->source)->toBe('stripe')
        ->and($mandates->created())->toHaveCount(2);
});

it('lists the mandates of a contact newest first and returns the newest active one', function () {
    $mandates = new FakeSepaMandates;
    $first = $mandates->create(new NewSepaMandate(contactId: 1, reference: 'M-1', signedAt: '2026-10-01'));
    $second = $mandates->create(new NewSepaMandate(contactId: 1, reference: 'M-2', signedAt: '2026-10-02'));
    $mandates->create(new NewSepaMandate(contactId: 2, reference: 'M-3', signedAt: '2026-10-03'));

    expect(array_map(fn ($mandate) => $mandate->id, $mandates->forContact(1)))->toBe([$second->id, $first->id])
        ->and($mandates->active(1)?->id)->toBe($second->id)
        ->and($mandates->active(9))->toBeNull();

    $mandates->revoke($second->id, 'replaced');

    expect($mandates->active(1)?->id)->toBe($first->id);
});

it('rejects a taken reference and a repeated external id', function () {
    $mandates = new FakeSepaMandates;
    $mandates->create(new NewSepaMandate(contactId: 1, reference: 'M-1', signedAt: '2026-10-01', source: 'stripe', externalId: 'sepa_1'));

    expect(fn () => $mandates->create(new NewSepaMandate(contactId: 2, reference: 'M-1', signedAt: '2026-10-01')))
        ->toThrow(ContractException::class)
        ->and(fn () => $mandates->create(new NewSepaMandate(contactId: 2, reference: 'M-2', signedAt: '2026-10-01', source: 'stripe', externalId: 'sepa_1')))
        ->toThrow(ContractException::class);
});

it('revokes once, keeps a revoked mandate revoked and rejects unknown ids', function () {
    $mandates = new FakeSepaMandates;
    $mandate = $mandates->create(new NewSepaMandate(contactId: 1, reference: 'M-1', signedAt: '2026-10-01'));

    $revoked = $mandates->revoke($mandate->id, 'customer request');
    $again = $mandates->revoke($mandate->id, 'other reason');

    expect($revoked->status)->toBe('revoked')
        ->and($revoked->revocationReason)->toBe('customer request')
        ->and($again->revocationReason)->toBe('customer request')
        ->and($mandates->revocations())->toHaveCount(1)
        ->and($mandates->find($mandate->id)?->status)->toBe('revoked')
        ->and(fn () => $mandates->revoke(99, 'x'))->toThrow(ContractException::class);
});
