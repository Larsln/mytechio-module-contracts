<?php

declare(strict_types=1);

use MyTechIO\Contracts\Invoices\InvoiceRef;

it('allows a null number for drafts', function () {
    $ref = new InvoiceRef(id: 1, number: null, status: 'draft');

    expect($ref->toArray())->toBe([
        'id' => 1,
        'number' => null,
        'status' => 'draft',
    ]);
});
