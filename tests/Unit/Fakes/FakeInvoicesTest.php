<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeInvoices;
use MyTechIO\Contracts\Invoices\InvoiceDocument;
use MyTechIO\Contracts\Invoices\InvoiceDraft;
use MyTechIO\Contracts\Invoices\InvoiceItemRef;
use MyTechIO\Contracts\Invoices\InvoiceRef;
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

it('finds created drafts and seeded invoices by id, or null when unknown', function () {
    $invoices = new FakeInvoices;
    $draft = $invoices->createDraft(new InvoiceDraft(contactId: 1, items: []));
    $invoices->seedInvoice(new InvoiceRef(id: 40, number: 'RE-40', status: 'sent', grossTotal: '10.0000', openAmount: '10.0000'));

    expect($invoices->find($draft->id)?->status)->toBe('draft')
        ->and($invoices->find(40)?->openAmount)->toBe('10.0000')
        ->and($invoices->find(999))->toBeNull();
});

it('makes one draft invoice per order with the payment method asked for', function () {
    $invoices = new FakeInvoices;
    $actor = new ActorRef(3, 'Lars');

    $draft = $invoices->createFromOrder(55, $actor, 'card');

    expect($draft->status)->toBe('draft')
        ->and($draft->number)->toBeNull()
        ->and($draft->orderId)->toBe(55)
        ->and($draft->paymentMethod)->toBe('card')
        ->and($invoices->createFromOrder(55, $actor)->id)->toBe($draft->id)
        ->and($invoices->createFromOrder(56, $actor)->paymentMethod)->toBe('bank_transfer')
        ->and(fn () => $invoices->createFromOrder(57, $actor, 'cash'))->toThrow(ContractException::class, 'Zahlart');
});

it('issues and sends an invoice once and picks up where a failure left it', function () {
    $invoices = new FakeInvoices;
    $actor = new ActorRef(3, 'Lars');
    $id = $invoices->createFromOrder(55, $actor, 'card')->id;

    $invoices->failNextIssue('Die Steuerprüfung fehlt.');
    expect(fn () => $invoices->issueAndSend($id, $actor))->toThrow(ContractException::class, 'Steuerprüfung')
        ->and($invoices->find($id)?->status)->toBe('draft');

    $invoices->failNextSend();
    expect(fn () => $invoices->issueAndSend($id, $actor))->toThrow(ContractException::class, 'verschickt')
        ->and($invoices->find($id)?->status)->toBe('issued')
        ->and($invoices->find($id)?->number)->toBe('RE-FAKE-00001')
        ->and($invoices->sent())->toBe([]);

    $sent = $invoices->issueAndSend($id, $actor);

    expect($sent->status)->toBe('awaiting_payment')
        ->and($sent->sentAt)->not->toBeNull()
        ->and($sent->paymentMethod)->toBe('card')
        ->and($invoices->issueAndSend($id, $actor)->number)->toBe('RE-FAKE-00001')
        ->and($invoices->sent())->toBe([$id])
        ->and(fn () => $invoices->issueAndSend(99, $actor))->toThrow(ContractException::class);
});

it('lists the issued invoices of an order and hands out their papers', function () {
    $invoices = new FakeInvoices;
    $actor = new ActorRef(3, 'Lars');
    $id = $invoices->createFromOrder(55, $actor, 'card')->id;
    $other = $invoices->createFromOrder(56, $actor)->id;

    // A draft is no paper yet.
    expect($invoices->forOrder(55))->toBe([])
        ->and($invoices->document($id))->toBeNull()
        ->and($invoices->document(999))->toBeNull();

    $invoices->issueAndSend($id, $actor);
    $invoices->seedInvoice(new InvoiceRef(id: 70, number: 'ST-1', status: 'issued', type: 'cancellation', orderId: 55, cancelsInvoiceId: $id));

    expect(array_map(static fn (InvoiceRef $invoice): int => $invoice->id, $invoices->forOrder(55)))->toBe([$id, 70])
        ->and($invoices->forOrder(56))->toBe([])
        ->and($invoices->document($other))->toBeNull();

    $made = $invoices->document($id);
    expect($made)->toBeInstanceOf(InvoiceDocument::class)
        ->and($made->mimeType)->toBe('application/pdf')
        ->and($made->filename)->toBe('RE-FAKE-00001.pdf');

    $invoices->seedDocument($id, new InvoiceDocument('%PDF-1.7 real', 'application/pdf', 'Rechnung.pdf'));
    expect($invoices->document($id)?->content)->toBe('%PDF-1.7 real');
});
