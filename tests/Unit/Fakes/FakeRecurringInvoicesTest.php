<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeRecurringInvoices;
use MyTechIO\Contracts\Invoices\RecurringBillingStatus;
use MyTechIO\Contracts\Invoices\RecurringInvoiceDraft;
use MyTechIO\Contracts\Invoices\RecurringInvoiceSummary;
use MyTechIO\Contracts\Invoices\RecurringItemDraft;

function fakeDraft(int $contactId = 5): RecurringInvoiceDraft
{
    return new RecurringInvoiceDraft(
        contactId: $contactId, title: 'Hosting', intervalUnit: 'month', intervalCount: 1,
        startDate: '2026-11-01',
        items: [new RecurringItemDraft('a.example', '1.0000', null, '5.0000')],
        paymentTermsDays: null, introductionText: null, actor: new ActorRef(null, 'job'),
    );
}

it('creates templates, records drafts and lists them per contact', function () {
    $fake = new FakeRecurringInvoices;

    $ref = $fake->create(fakeDraft());
    $fake->create(fakeDraft(9));

    expect($ref->id)->toBe(1)
        ->and($fake->forContact(5))->toHaveCount(1)
        ->and($fake->find(1)->nextRunDate)->toBe('2026-11-01')
        ->and($fake->find(1)->items[0]->description)->toBe('a.example')
        ->and($fake->drafts())->toHaveCount(2)
        ->and($fake->find(99))->toBeNull();
});

it('adds items with fresh ids', function () {
    $fake = new FakeRecurringInvoices;
    $id = $fake->create(fakeDraft())->id;

    $fake->addItems($id, [new RecurringItemDraft('b.example', '1.0000', null, '6.0000')]);

    expect($fake->find($id)->items)->toHaveCount(2)
        ->and($fake->find($id)->items[1]->id)->toBe(2);
});

it('ends a template and records the ending', function () {
    $fake = new FakeRecurringInvoices;
    $id = $fake->create(fakeDraft())->id;

    $fake->endAt($id, '2026-10-31', new ActorRef(1, 'Admin'));

    expect($fake->find($id)->endDate)->toBe('2026-10-31')
        ->and($fake->find($id)->nextRunDate)->toBeNull()
        ->and($fake->endings())->toHaveCount(1);
});

it('answers billing status with zeros unless seeded', function () {
    $fake = new FakeRecurringInvoices;
    $id = $fake->create(fakeDraft())->id;

    expect($fake->billingStatus($id)->openAmountGross)->toBe('0.0000');

    $fake->seedBillingStatus(new RecurringBillingStatus($id, '2026-10-31', '2026-11-01', 3, 1, '10.0000', 2));

    expect($fake->billingStatus($id)->invoicedCount)->toBe(3);
});

it('seeds templates and throws for unknown ids', function () {
    $fake = new FakeRecurringInvoices;
    $fake->seed(new RecurringInvoiceSummary(7, 5, 'X', 'year', 1, '2026-01-01', null, null, true));

    expect($fake->find(7))->not->toBeNull()
        ->and($fake->create(fakeDraft())->id)->toBe(8);

    $fake->endAt(99, '2026-12-31', new ActorRef(null, 'x'));
})->throws(ContractException::class);
