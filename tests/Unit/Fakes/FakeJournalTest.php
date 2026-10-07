<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\AccountInfo;
use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Accounting\JournalEntryDraft;
use MyTechIO\Contracts\Accounting\JournalLineDraft;
use MyTechIO\Contracts\Accounting\Side;
use MyTechIO\Contracts\Accounting\UnbalancedEntryException;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeJournal;

it('posts a balanced entry and returns an entry ref', function () {
    $journal = new FakeJournal;

    $ref = $journal->post(new JournalEntryDraft(
        bookedOn: '2026-10-06',
        description: 'Testbuchung',
        lines: [
            new JournalLineDraft(accountNumber: '1200', side: Side::Debit, amount: '119.0000'),
            new JournalLineDraft(accountNumber: '8400', side: Side::Credit, amount: '119.0000'),
        ],
    ));

    expect($ref->bookedOn)->toBe('2026-10-06')
        ->and($journal->posted())->toHaveCount(1);
});

it('throws UnbalancedEntryException when debit does not equal credit', function () {
    $journal = new FakeJournal;

    $post = fn () => $journal->post(new JournalEntryDraft(
        bookedOn: '2026-10-06',
        description: 'Testbuchung',
        lines: [
            new JournalLineDraft(accountNumber: '1200', side: Side::Debit, amount: '100.0000'),
            new JournalLineDraft(accountNumber: '8400', side: Side::Credit, amount: '99.0000'),
        ],
    ));

    expect($post)->toThrow(UnbalancedEntryException::class);
});

it('balances amounts with more than two decimal places without float errors', function () {
    $journal = new FakeJournal;

    // 3 × 33.3334 = 100.0002, must balance exactly against a single counter-entry.
    $ref = $journal->post(new JournalEntryDraft(
        bookedOn: '2026-10-06',
        description: 'Rundungstest',
        lines: [
            new JournalLineDraft(accountNumber: '1200', side: Side::Debit, amount: '33.3334'),
            new JournalLineDraft(accountNumber: '1200', side: Side::Debit, amount: '33.3334'),
            new JournalLineDraft(accountNumber: '1200', side: Side::Debit, amount: '33.3334'),
            new JournalLineDraft(accountNumber: '8400', side: Side::Credit, amount: '100.0002'),
        ],
    ));

    expect($ref)->not->toBeNull();
});

it('reverses a posted entry by swapping debit and credit', function () {
    $journal = new FakeJournal;

    $original = $journal->post(new JournalEntryDraft(
        bookedOn: '2026-10-06',
        description: 'Testbuchung',
        lines: [
            new JournalLineDraft(accountNumber: '1200', side: Side::Debit, amount: '119.0000'),
            new JournalLineDraft(accountNumber: '8400', side: Side::Credit, amount: '119.0000'),
        ],
    ));

    $reversal = $journal->reverse($original->id, '2026-10-07', 'Storno', new ActorRef(id: 1, label: 'Admin'));

    expect($reversal->id)->not->toBe($original->id)
        ->and($journal->posted())->toHaveCount(2);
});

it('throws when reversing an unknown entry id', function () {
    $journal = new FakeJournal;

    $reverse = fn () => $journal->reverse('unknown', '2026-10-07', 'Storno', new ActorRef(id: 1, label: 'Admin'));

    expect($reverse)->toThrow(ContractException::class);
});

it('finds a seeded account by number', function () {
    $journal = new FakeJournal;
    $journal->seedAccount(new AccountInfo(number: '1200', name: 'Bank', type: 'asset'));

    expect($journal->findAccount('1200')?->name)->toBe('Bank')
        ->and($journal->findAccount('9999'))->toBeNull();
});
