<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Accounting\JournalEntryDraft;
use MyTechIO\Contracts\Accounting\JournalLineDraft;
use MyTechIO\Contracts\Accounting\Side;

it('converts nested lines and actor to arrays', function () {
    $draft = new JournalEntryDraft(
        bookedOn: '2026-10-06',
        description: 'Testbuchung',
        lines: [
            new JournalLineDraft(accountNumber: '1200', side: Side::Debit, amount: '119.0000'),
            new JournalLineDraft(accountNumber: '8400', side: Side::Credit, amount: '119.0000'),
        ],
        actor: new ActorRef(id: 1, label: 'Admin'),
    );

    $array = $draft->toArray();

    expect($array['lines'])->toHaveCount(2)
        ->and($array['lines'][0]['side'])->toBe('debit')
        ->and($array['actor'])->toBe(['id' => 1, 'label' => 'Admin']);
});

it('converts a null actor to null', function () {
    $draft = new JournalEntryDraft(bookedOn: '2026-10-06', description: 'Testbuchung', lines: []);

    expect($draft->toArray()['actor'])->toBeNull();
});
