<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeInvoices;
use MyTechIO\Contracts\Invoices\InvoiceDraft;
use MyTechIO\Contracts\Invoices\InvoiceItemRef;
use MyTechIO\Contracts\Invoices\PaidDocument;

it('creates drafts with ascending ids, null number and draft status', function () {
    $invoices = new FakeInvoices;

    $first = $invoices->createDraft(new InvoiceDraft(contactId: 1, items: []));
    $second = $invoices->createDraft(new InvoiceDraft(contactId: 2, items: []));

    expect($first->id)->toBe(1)
        ->and($first->number)->toBeNull()
        ->and($first->status)->toBe('draft')
        ->and($second->id)->toBe(2)
        ->and($invoices->drafts())->toHaveCount(2);
});

it('finds seeded items by id, or null when unknown', function () {
    $invoices = new FakeInvoices;

    $invoices->seedItem(new InvoiceItemRef(
        id: 7,
        invoiceId: 12,
        invoiceNumber: 'RE-2026-0012',
        invoiceStatus: 'sent',
        description: 'Domain example.com',
        url: '/invoices/12',
    ));

    $found = $invoices->findItem(7);

    expect($found)->not->toBeNull()
        ->and($found->invoiceId)->toBe(12)
        ->and($found->invoiceNumber)->toBe('RE-2026-0012')
        ->and($invoices->findItem(999))->toBeNull();
});

it('returns paid documents within the range, sorted by paidOn', function () {
    $invoices = new FakeInvoices;
    $invoices->seedPaidDocument(new PaidDocument('invoice', 2, 'RE-2', '2026-09-20', '50.0000', null, '/invoices/2'));
    $invoices->seedPaidDocument(new PaidDocument('invoice', 1, 'RE-1', '2026-09-10', '100.0000', 3, '/invoices/1'));
    $invoices->seedPaidDocument(new PaidDocument('invoice', 3, 'RE-3', '2026-10-05', '20.0000', null, '/invoices/3'));

    $result = $invoices->paidBetween('2026-09-01', '2026-09-30');

    expect($result)->toHaveCount(2)
        ->and($result[0]->id)->toBe(1)
        ->and($result[1]->id)->toBe(2)
        ->and($result[0]->type)->toBe('invoice');
});
