<?php

declare(strict_types=1);

use MyTechIO\Contracts\Contacts\ContactData;
use MyTechIO\Contracts\Fakes\FakeContacts;

function makeContact(int $id, string $displayName, string $direction = 'customer'): ContactData
{
    return new ContactData($id, $displayName, 'company', $direction, $displayName, null, null, null, null, null, null, null, null, null, null, null);
}

it('finds a seeded contact by id', function () {
    $contacts = new FakeContacts;
    $contacts->seed(makeContact(1, 'Kunde GmbH'));

    expect($contacts->find(1)?->displayName)->toBe('Kunde GmbH')
        ->and($contacts->find(999))->toBeNull();
});

it('searches case-insensitively by display name with a limit', function () {
    $contacts = new FakeContacts;
    $contacts->seed(makeContact(1, 'Kunde GmbH'));
    $contacts->seed(makeContact(2, 'Anderer Kunde'));
    $contacts->seed(makeContact(3, 'Dritte Firma'));

    $matches = $contacts->search('kunde', limit: 1);

    expect($matches)->toHaveCount(1);

    $allMatches = $contacts->search('kunde', limit: 20);
    expect($allMatches)->toHaveCount(2);
});

it('returns default ISO-2 country names and allows overriding them', function () {
    $contacts = new FakeContacts;

    expect($contacts->countries())->toHaveKey('DE');

    $contacts->setCountries(['FR' => 'Frankreich']);

    expect($contacts->countries())->toBe(['FR' => 'Frankreich']);
});

it('lists supplier-side contacts ordered by display name', function () {
    $contacts = new FakeContacts;
    $contacts->seed(makeContact(1, 'Zeta Handel', 'supplier'));
    $contacts->seed(makeContact(2, 'Kunde GmbH', 'customer'));
    $contacts->seed(makeContact(3, 'alpha Werkzeug', 'both'));
    $contacts->seed(makeContact(4, 'Mitte AG', 'supplier'));

    expect(array_map(fn (ContactData $c) => $c->id, $contacts->suppliers()))->toBe([3, 4, 1])
        ->and((new FakeContacts)->suppliers())->toBe([]);
});
