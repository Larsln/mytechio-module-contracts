<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\JournalEntryRef;

it('converts to an array with snake_case keys', function () {
    $ref = new JournalEntryRef(id: '1', entryNumber: 'F-0001', bookedOn: '2026-10-06');

    expect($ref->toArray())->toBe([
        'id' => '1',
        'entry_number' => 'F-0001',
        'booked_on' => '2026-10-06',
        'source_type' => null,
        'source_id' => null,
        'document_reference' => null,
    ]);
});

it('carries the source of the booking', function () {
    $ref = new JournalEntryRef('1', 'F-0001', '2026-10-06', 'App\\Models\\StockVoucher', 'LB-2026-00001', 'LB-2026-00001');

    expect($ref->sourceType)->toBe('App\\Models\\StockVoucher')
        ->and($ref->sourceId)->toBe('LB-2026-00001')
        ->and($ref->toArray()['document_reference'])->toBe('LB-2026-00001');
});
