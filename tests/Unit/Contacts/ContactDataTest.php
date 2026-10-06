<?php

declare(strict_types=1);

use MyTechIO\Contracts\Contacts\ContactData;

it('converts to an array with snake_case keys', function () {
    $contact = new ContactData(
        id: 1,
        displayName: 'Kunde GmbH',
        type: 'company',
        direction: 'customer',
        companyName: 'Kunde GmbH',
        firstName: null,
        lastName: null,
        email: 'kontakt@kunde.example',
        phone: '+49 30 1234567',
        street: 'Musterstraße',
        houseNumber: '1',
        addressAddition: null,
        postalCode: '10115',
        city: 'Berlin',
        countryCode: 'DE',
        vatId: 'DE123456789',
    );

    expect($contact->toArray())->toBe([
        'id' => 1,
        'display_name' => 'Kunde GmbH',
        'type' => 'company',
        'direction' => 'customer',
        'company_name' => 'Kunde GmbH',
        'first_name' => null,
        'last_name' => null,
        'email' => 'kontakt@kunde.example',
        'phone' => '+49 30 1234567',
        'street' => 'Musterstraße',
        'house_number' => '1',
        'address_addition' => null,
        'postal_code' => '10115',
        'city' => 'Berlin',
        'country_code' => 'DE',
        'vat_id' => 'DE123456789',
    ]);
});
