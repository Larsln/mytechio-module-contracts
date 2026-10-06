<?php

declare(strict_types=1);

use MyTechIO\Contracts\Mail\MailAttachment;

it('defaults mimeType to null', function () {
    $attachment = new MailAttachment(name: 'rechnung.pdf', path: 'documents/invoices/2026/10/rechnung.pdf');

    expect($attachment->mimeType)->toBeNull();
});

it('converts to an array with snake_case keys', function () {
    $attachment = new MailAttachment(name: 'rechnung.pdf', path: '/tmp/rechnung.pdf', mimeType: 'application/pdf');

    expect($attachment->toArray())->toBe([
        'name' => 'rechnung.pdf',
        'path' => '/tmp/rechnung.pdf',
        'mime_type' => 'application/pdf',
    ]);
});
