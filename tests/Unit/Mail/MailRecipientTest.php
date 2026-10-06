<?php

declare(strict_types=1);

use MyTechIO\Contracts\Mail\MailRecipient;

it('defaults role to to and contactId to null', function () {
    $recipient = new MailRecipient(email: 'kunde@example.com', displayName: 'Kunde GmbH');

    expect($recipient->role)->toBe('to')
        ->and($recipient->contactId)->toBeNull();
});

it('converts to an array with snake_case keys', function () {
    $recipient = new MailRecipient(email: 'kunde@example.com', displayName: 'Kunde GmbH', contactId: 5, role: 'cc');

    expect($recipient->toArray())->toBe([
        'email' => 'kunde@example.com',
        'display_name' => 'Kunde GmbH',
        'contact_id' => 5,
        'role' => 'cc',
    ]);
});
