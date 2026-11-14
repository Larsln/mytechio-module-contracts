<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Contacts\ContactChanges;
use MyTechIO\Contracts\Contacts\ContactData;
use MyTechIO\Contracts\Contacts\ContactNotFoundException;
use MyTechIO\Contracts\Contacts\ContactValidationException;
use MyTechIO\Contracts\Contacts\NewContact;
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

it('finds several contacts by id keyed by id and leaves unknown ids out', function () {
    $contacts = new FakeContacts;
    $contacts->seed(makeContact(1, 'Kunde GmbH'));
    $contacts->seed(makeContact(2, 'Anderer Kunde'));
    $contacts->seed(makeContact(3, 'Dritte Firma'));

    $found = $contacts->findMany([3, 1, 999]);

    expect(array_keys($found))->toEqualCanonicalizing([1, 3])
        ->and($found[3]->displayName)->toBe('Dritte Firma')
        ->and($contacts->findMany([]))->toBe([]);
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

function newShopContact(array $overrides = []): NewContact
{
    return new NewContact(...[
        'type' => 'b2c', 'salutation' => 'Frau', 'companyName' => null, 'firstName' => 'Erika', 'lastName' => 'Muster',
        'email' => 'erika@example.test', 'phone' => null, 'street' => 'Weg', 'houseNumber' => '1', 'addressAddition' => null,
        'postalCode' => '52062', 'city' => 'Aachen', 'countryCode' => 'DE', 'vatId' => null,
        ...$overrides,
    ]);
}

it('creates customer contacts with ascending ids, customer numbers and the actor recorded', function () {
    $contacts = new FakeContacts;
    $actor = new ActorRef(null, 'Shop (automatisch)');

    $person = $contacts->create(newShopContact(), $actor);
    $company = $contacts->create(newShopContact(['type' => 'b2b', 'companyName' => 'Acme GmbH', 'email' => 'einkauf@acme.example', 'vatId' => 'de 123 456 789']), $actor);

    expect($person->id)->toBe(1)
        ->and($person->displayName)->toBe('Erika Muster')
        ->and($person->direction)->toBe('customer')
        ->and($person->customerNumber)->toBe('K-00001')
        ->and($company->displayName)->toBe('Acme GmbH')
        ->and($company->vatId)->toBe('DE123456789')
        ->and($company->vatIdValid)->toBeTrue()
        ->and($contacts->find(2)?->email)->toBe('einkauf@acme.example')
        ->and($contacts->actors())->toHaveCount(2)
        ->and($contacts->actors()[0]['actor']->label)->toBe('Shop (automatisch)');
});

it('finds contacts by exact email regardless of case', function () {
    $contacts = new FakeContacts;
    $actor = new ActorRef(1, 'Lars');
    $contacts->create(newShopContact(), $actor);
    $contacts->create(newShopContact(['email' => 'Erika@Example.test', 'lastName' => 'Zwei']), $actor);
    $contacts->create(newShopContact(['email' => 'other@example.test']), $actor);

    expect(array_map(fn ($c) => $c->id, $contacts->findByEmail('ERIKA@example.test')))->toBe([1, 2])
        ->and($contacts->findByEmail('nobody@example.test'))->toBe([]);
});

it('refuses missing required fields, a VAT ID on a person and a foreign VAT prefix', function () {
    $contacts = new FakeContacts;
    $actor = new ActorRef(1, 'Lars');

    try {
        $contacts->create(newShopContact(['lastName' => '', 'email' => 'nope', 'city' => '']), $actor);
        $this->fail('expected a validation exception');
    } catch (ContactValidationException $e) {
        expect(array_keys($e->errors))->toBe(['city', 'last_name', 'email']);
    }

    expect(fn () => $contacts->create(newShopContact(['vatId' => 'DE123456789']), $actor))->toThrow(ContactValidationException::class, 'nur für B2B')
        ->and(fn () => $contacts->create(newShopContact(['type' => 'b2b', 'companyName' => 'Acme', 'countryCode' => 'BE', 'vatId' => 'DE123456789']), $actor))->toThrow(ContactValidationException::class, 'Adressland (BE)');
});

it('reports VAT IDs as declared and refuses invalid or uncheckable ones', function () {
    $contacts = new FakeContacts;
    $actor = new ActorRef(1, 'Lars');
    $contacts->vatIdValidity('DE999999999', false);
    $contacts->vatIdValidity('DE888888888', null);
    $company = ['type' => 'b2b', 'companyName' => 'Acme GmbH'];

    expect(fn () => $contacts->create(newShopContact([...$company, 'vatId' => 'DE999999999']), $actor))->toThrow(ContactValidationException::class, 'ungültig')
        ->and(fn () => $contacts->create(newShopContact([...$company, 'vatId' => 'DE888888888']), $actor))->toThrow(ContactValidationException::class, 'nicht erreichbar');

    $contacts->vatIdValidity('DE777777777', true);
    $created = $contacts->create(newShopContact([...$company, 'vatId' => 'DE777777777']), $actor);

    expect($created->vatIdValid)->toBeTrue();
});

it('replaces the customer-facing fields on update and keeps the rest', function () {
    $contacts = new FakeContacts;
    $actor = new ActorRef(1, 'Lars');
    $contacts->seed(new ContactData(5, 'Acme GmbH', 'b2b', 'both', 'Acme GmbH', null, null, 'info@acme.example', '1', 'Weg', '1', null, '1', 'Aachen', 'DE', null, mobile: '+49 170', website: 'https://acme.example', customerNumber: 'K-00005'));

    $updated = $contacts->update(5, new ContactChanges(null, 'Acme AG', 'Erika', 'Muster', 'shop@acme.example', null, 'Neuer Weg', '2', null, '52062', 'Aachen', 'DE', 'DE123456789'), $actor);

    expect($updated->companyName)->toBe('Acme AG')
        ->and($updated->displayName)->toBe('Acme AG')
        ->and($updated->email)->toBe('shop@acme.example')
        ->and($updated->phone)->toBeNull()
        ->and($updated->direction)->toBe('both')
        ->and($updated->mobile)->toBe('+49 170')
        ->and($updated->customerNumber)->toBe('K-00005')
        ->and($updated->vatId)->toBe('DE123456789')
        ->and($contacts->actors())->toBe([['action' => 'update', 'contact_id' => 5, 'actor' => $actor]])
        ->and(fn () => $contacts->update(99, new ContactChanges(null, null, 'A', 'B', 'a@b.test', null, 'W', '1', null, '1', 'C', 'DE'), $actor))->toThrow(ContactNotFoundException::class);
});
