<?php

declare(strict_types=1);

use MyTechIO\Contracts\Mail\MailAttachment;
use MyTechIO\Contracts\Mail\MailRecipient;
use MyTechIO\Contracts\Mail\OutgoingMail;

it('defaults attachments and owner fields', function () {
    $mail = new OutgoingMail(
        subject: 'Betreff',
        bodyHtml: '<p>Hallo</p>',
        recipients: [new MailRecipient(email: 'kunde@example.com', displayName: 'Kunde')],
    );

    expect($mail->attachments)->toBe([])
        ->and($mail->ownerType)->toBeNull()
        ->and($mail->ownerId)->toBeNull()
        ->and($mail->template)->toBeNull();
});

it('converts nested recipients and attachments to arrays', function () {
    $mail = new OutgoingMail(
        subject: 'Betreff',
        bodyHtml: '<p>Hallo</p>',
        recipients: [new MailRecipient(email: 'kunde@example.com', displayName: 'Kunde')],
        attachments: [new MailAttachment(name: 'anhang.pdf', path: '/tmp/anhang.pdf')],
        ownerType: 'invoice',
        ownerId: 1,
        template: 'invoice_sent',
    );

    expect($mail->toArray())->toBe([
        'subject' => 'Betreff',
        'body_html' => '<p>Hallo</p>',
        'recipients' => [[
            'email' => 'kunde@example.com',
            'display_name' => 'Kunde',
            'contact_id' => null,
            'role' => 'to',
        ]],
        'attachments' => [[
            'name' => 'anhang.pdf',
            'path' => '/tmp/anhang.pdf',
            'mime_type' => null,
        ]],
        'owner_type' => 'invoice',
        'owner_id' => 1,
        'template' => 'invoice_sent',
    ]);
});
