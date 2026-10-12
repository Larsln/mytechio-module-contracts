<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\IncomingInvoiceItemExtension;
use MyTechIO\Contracts\Fakes\FakeIncomingInvoiceItemExtension;

it('implements the contract and keeps its key', function () {
    $extension = new FakeIncomingInvoiceItemExtension('fixed-assets');

    expect($extension)->toBeInstanceOf(IncomingInvoiceItemExtension::class)
        ->and($extension->key())->toBe('fixed-assets')
        ->and((new FakeIncomingInvoiceItemExtension)->key())->toBe('fake');
});

it('records the calls and returns the configured results', function () {
    $extension = new FakeIncomingInvoiceItemExtension;
    $extension->withValidationErrors(['kind' => 'Pflichtfeld']);
    $extension->withAnnotationLines(['Anlagegut']);

    expect($extension->validate(['kind' => null], 7))->toBe(['kind' => 'Pflichtfeld'])
        ->and($extension->annotate(3, ['kind' => 'pool']))->toBe(['Anlagegut']);

    $extension->afterItemsSynced(9, [['id' => 3, 'extras' => ['kind' => 'pool']]]);

    expect($extension->validateCalls)->toBe([['extras' => ['kind' => null], 'supplierContactId' => 7]])
        ->and($extension->annotateCalls)->toBe([['incomingInvoiceItemId' => 3, 'extras' => ['kind' => 'pool']]])
        ->and($extension->afterItemsSyncedCalls)->toBe([['invoiceId' => 9, 'items' => [['id' => 3, 'extras' => ['kind' => 'pool']]]]]);
});
