<?php

declare(strict_types=1);

use MyTechIO\Contracts\Contacts\ContactChanges;
use MyTechIO\Contracts\Contacts\ContactNotFoundException;
use MyTechIO\Contracts\Contacts\ContactValidationException;
use MyTechIO\Contracts\Contacts\NewContact;
use MyTechIO\Contracts\ContractException;

it('serializes a new contact with snake_case keys', function () {
    $contact = new NewContact('b2b', null, 'Acme GmbH', 'Erika', 'Muster', 'einkauf@acme.example', '+49 241 1', 'Hofgartenstraße', '14', null, '53840', 'Troisdorf', 'DE', 'DE123456789');

    expect($contact->toArray())->toBe([
        'type' => 'b2b', 'salutation' => null, 'company_name' => 'Acme GmbH', 'first_name' => 'Erika', 'last_name' => 'Muster',
        'email' => 'einkauf@acme.example', 'phone' => '+49 241 1', 'street' => 'Hofgartenstraße', 'house_number' => '14',
        'address_addition' => null, 'postal_code' => '53840', 'city' => 'Troisdorf', 'country_code' => 'DE', 'vat_id' => 'DE123456789',
    ]);
});

it('serializes contact changes and defaults the VAT ID to null', function () {
    $changes = new ContactChanges('Frau', null, 'Erika', 'Muster', 'erika@example.test', null, 'Weg', '1a', 'Hinterhaus', '52062', 'Aachen', 'DE');

    expect($changes->toArray())->toMatchArray(['salutation' => 'Frau', 'house_number' => '1a', 'address_addition' => 'Hinterhaus', 'vat_id' => null]);
});

it('joins field errors into the exception message', function () {
    $exception = new ContactValidationException(['email' => ['E-Mail fehlt.'], 'city' => ['Ort fehlt.', 'Ort zu lang.']]);

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->errors)->toBe(['email' => ['E-Mail fehlt.'], 'city' => ['Ort fehlt.', 'Ort zu lang.']])
        ->and($exception->getMessage())->toBe('E-Mail fehlt. Ort fehlt. Ort zu lang.')
        ->and(ContactValidationException::forField('vat_id', 'Ungültig.')->errors)->toBe(['vat_id' => ['Ungültig.']])
        ->and(ContactNotFoundException::forId(7)->getMessage())->toBe('Kontakt mit ID 7 wurde nicht gefunden.');
});
