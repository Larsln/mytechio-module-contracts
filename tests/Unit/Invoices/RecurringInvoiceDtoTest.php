<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Invoices\RecurringBillingStatus;
use MyTechIO\Contracts\Invoices\RecurringInvoiceDraft;
use MyTechIO\Contracts\Invoices\RecurringInvoiceRef;
use MyTechIO\Contracts\Invoices\RecurringInvoiceSummary;
use MyTechIO\Contracts\Invoices\RecurringItemDraft;
use MyTechIO\Contracts\Invoices\RecurringItemSummary;

function recurringDraft(string $unit = 'month', int $count = 1, string $start = '2026-11-01'): RecurringInvoiceDraft
{
    return new RecurringInvoiceDraft(
        contactId: 5,
        title: 'Hosting',
        intervalUnit: $unit,
        intervalCount: $count,
        startDate: $start,
        items: [new RecurringItemDraft('example.com', '1.0000', 'Stk', '12.0000', extras: ['customer-assets' => ['asset_ids' => [3]]])],
        paymentTermsDays: 14,
        introductionText: null,
        actor: new ActorRef(1, 'Admin'),
    );
}

it('converts a recurring draft with items and actor to an array', function () {
    $array = recurringDraft()->toArray();

    expect($array['contact_id'])->toBe(5)
        ->and($array['interval_unit'])->toBe('month')
        ->and($array['items'][0]['unit_price_net'])->toBe('12.0000')
        ->and($array['items'][0]['extras'])->toBe(['customer-assets' => ['asset_ids' => [3]]])
        ->and($array['actor'])->toBe(['id' => 1, 'label' => 'Admin']);
});

it('rejects invalid intervals and dates', function (string $unit, int $count, string $start) {
    recurringDraft($unit, $count, $start);
})->with([
    'unit' => ['week', 1, '2026-11-01'],
    'count' => ['month', 0, '2026-11-01'],
    'date' => ['month', 1, '2026-02-30'],
    'format' => ['month', 1, '01.11.2026'],
])->throws(ContractException::class);

it('rejects non-decimal amounts', function () {
    new RecurringItemDraft('x', '1', null, '1,50');
})->throws(ContractException::class);

it('converts summaries, refs and billing status to arrays', function () {
    $summary = new RecurringInvoiceSummary(
        id: 2, contactId: 5, title: 'Hosting', intervalUnit: 'year', intervalCount: 1,
        startDate: '2026-01-01', nextRunDate: '2027-01-01', endDate: null, isActive: true,
        items: [new RecurringItemSummary(9, 'example.com', '1.0000', '12.0000')],
    );
    $status = new RecurringBillingStatus(2, '2026-12-31', '2027-01-01', 1, 1, '14.2800', 4);

    expect($summary->toArray()['items'][0]['id'])->toBe(9)
        ->and($summary->toArray()['end_date'])->toBeNull()
        ->and((new RecurringInvoiceRef(2))->toArray())->toBe(['id' => 2])
        ->and($status->toArray()['open_amount_gross'])->toBe('14.2800')
        ->and($status->toArray()['oldest_open_due_days'])->toBe(4);
});

it('rejects a malformed billing status amount', function () {
    new RecurringBillingStatus(1, null, null, 0, 0, '0.00001', null);
})->throws(ContractException::class);
