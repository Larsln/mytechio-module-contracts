<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeMailOutbox;
use MyTechIO\Contracts\Mail\MailRecipient;
use MyTechIO\Contracts\Mail\OutgoingMail;

it('records sent mails and returns ascending outbound mail ids', function () {
    $outbox = new FakeMailOutbox;

    $mail = new OutgoingMail(subject: 'Betreff', bodyHtml: '<p>Hallo</p>', recipients: [
        new MailRecipient(email: 'kunde@example.com', displayName: 'Kunde'),
    ]);

    $first = $outbox->send($mail);
    $second = $outbox->send($mail);

    expect($first->outboundMailId)->toBe(1)
        ->and($second->outboundMailId)->toBe(2)
        ->and($outbox->sent())->toHaveCount(2);
});

it('finds mails sent to a given email address', function () {
    $outbox = new FakeMailOutbox;

    $toKunde = new OutgoingMail(subject: 'An Kunde', bodyHtml: '', recipients: [
        new MailRecipient(email: 'kunde@example.com', displayName: 'Kunde'),
    ]);
    $toAndere = new OutgoingMail(subject: 'An Andere', bodyHtml: '', recipients: [
        new MailRecipient(email: 'andere@example.com', displayName: 'Andere'),
    ]);

    $outbox->send($toKunde);
    $outbox->send($toAndere);

    $matches = $outbox->sentTo('kunde@example.com');

    expect($matches)->toHaveCount(1)
        ->and($matches[0]->subject)->toBe('An Kunde');
});
