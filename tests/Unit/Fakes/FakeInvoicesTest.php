<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeInvoices;
use MyTechIO\Contracts\Invoices\InvoiceDraft;
use MyTechIO\Contracts\Invoices\InvoiceItemRef;

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
