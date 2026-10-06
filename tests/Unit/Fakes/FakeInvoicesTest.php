<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeInvoices;
use MyTechIO\Contracts\Invoices\InvoiceDraft;

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
