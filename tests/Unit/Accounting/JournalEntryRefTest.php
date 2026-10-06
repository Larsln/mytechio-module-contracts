<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\JournalEntryRef;

it('converts to an array with snake_case keys', function () {
    $ref = new JournalEntryRef(id: '1', entryNumber: 'F-0001', bookedOn: '2026-10-06');

    expect($ref->toArray())->toBe([
        'id' => '1',
        'entry_number' => 'F-0001',
        'booked_on' => '2026-10-06',
    ]);
});
