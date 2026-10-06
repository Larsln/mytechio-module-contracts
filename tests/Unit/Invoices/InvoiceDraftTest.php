<?php

declare(strict_types=1);

use MyTechIO\Contracts\Invoices\InvoiceDraft;
use MyTechIO\Contracts\Invoices\InvoiceDraftItem;

it('converts nested items to arrays', function () {
    $draft = new InvoiceDraft(
        contactId: 1,
        items: [new InvoiceDraftItem(description: 'Domain example.de', quantity: '1.0000', unitPriceNet: '19.9900')],
    );

    $array = $draft->toArray();

    expect($array['items'])->toHaveCount(1)
        ->and($array['items'][0]['description'])->toBe('Domain example.de')
        ->and($array['payment_terms_days'])->toBeNull()
        ->and($array['bank_account_id'])->toBeNull();
});
