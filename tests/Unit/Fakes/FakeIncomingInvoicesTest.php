<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\IncomingInvoiceRef;
use MyTechIO\Contracts\Fakes\FakeIncomingInvoices;
use MyTechIO\Contracts\Invoices\PaidDocument;

it('finds a seeded incoming invoice by id, or null when unknown', function () {
    $invoices = new FakeIncomingInvoices;
    $invoices->seed(new IncomingInvoiceRef(
        id: 9,
        number: 'ER-2026-0003',
        status: 'booked',
        contactId: 4,
        url: '/incoming-invoices/9',
    ));

    expect($invoices->find(9)?->number)->toBe('ER-2026-0003')
        ->and($invoices->find(999))->toBeNull();
});

it('returns paid documents within the range, sorted by paidOn', function () {
    $invoices = new FakeIncomingInvoices;
    $invoices->seedPaidDocument(new PaidDocument('incoming_invoice', 2, 'ER-2', '2026-09-20', '50.0000', null, '/incoming-invoices/2'));
    $invoices->seedPaidDocument(new PaidDocument('incoming_invoice', 1, 'ER-1', '2026-09-10', '100.0000', 3, '/incoming-invoices/1'));
    $invoices->seedPaidDocument(new PaidDocument('incoming_invoice', 3, 'ER-3', '2026-10-05', '20.0000', null, '/incoming-invoices/3'));

    $result = $invoices->paidBetween('2026-09-01', '2026-09-30');

    expect($result)->toHaveCount(2)
        ->and($result[0]->id)->toBe(1)
        ->and($result[1]->id)->toBe(2)
        ->and($result[0]->type)->toBe('incoming_invoice');
});
