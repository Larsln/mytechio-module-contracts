<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\JournalEntryDraft;
use MyTechIO\Contracts\Accounting\JournalEntryRef;
use MyTechIO\Contracts\Accounting\JournalLineDraft;
use MyTechIO\Contracts\Accounting\JournalSourceResolver;
use MyTechIO\Contracts\Accounting\Side;
use MyTechIO\Contracts\Fakes\FakeJournal;
use MyTechIO\Contracts\Fakes\FakeJournalSourceResolver;

function resolverEntry(?string $type = 'fake.source', ?string $id = '42', ?string $reference = null): JournalEntryRef
{
    return new JournalEntryRef('1', 'F-0001', '2026-10-12', $type, $id, $reference);
}

it('implements the resolver contract and lists its source types', function () {
    $resolver = new FakeJournalSourceResolver(['a.source', 'b.source']);

    expect($resolver)->toBeInstanceOf(JournalSourceResolver::class)
        ->and($resolver->sourceTypes())->toBe(['a.source', 'b.source']);
});

it('links entries of its source types by the source id and prefers the document reference as label', function () {
    $resolver = new FakeJournalSourceResolver(urlPrefix: '/stock-vouchers');

    expect($resolver->link(resolverEntry())?->toArray())->toBe(['label' => '42', 'url' => '/stock-vouchers/42'])
        ->and($resolver->link(resolverEntry(reference: 'LB-1'))?->label)->toBe('LB-1')
        ->and($resolver->linkCalls)->toHaveCount(2);
});

it('does not claim foreign or source-less entries', function () {
    $resolver = new FakeJournalSourceResolver(reversalHint: 'Nur per Storno.');

    expect($resolver->link(resolverEntry('other.source')))->toBeNull()
        ->and($resolver->link(resolverEntry(id: null)))->toBeNull()
        ->and($resolver->reversalHint(resolverEntry('other.source')))->toBeNull();
});

it('returns the configured reversal hint for its own entries', function () {
    $resolver = new FakeJournalSourceResolver;

    expect($resolver->reversalHint(resolverEntry()))->toBeNull();

    $resolver->withReversalHint('Korrektur nur über einen Storno-Beleg.');

    expect($resolver->reversalHint(resolverEntry()))->toBe('Korrektur nur über einen Storno-Beleg.')
        ->and($resolver->reversalHintCalls)->toHaveCount(2);
});

it('receives the source of a posted entry through the fake journal', function () {
    $journal = new FakeJournal;
    $ref = $journal->post(new JournalEntryDraft(
        bookedOn: '2026-10-12',
        description: 'Test',
        lines: [
            new JournalLineDraft('1000', Side::Debit, '1.0000'),
            new JournalLineDraft('1200', Side::Credit, '1.0000'),
        ],
        sourceType: 'fake.source',
        sourceId: '7',
        documentReference: 'REF-7',
    ));

    expect((new FakeJournalSourceResolver)->link($ref)?->toArray())->toBe(['label' => 'REF-7', 'url' => '/fake/7']);
});
