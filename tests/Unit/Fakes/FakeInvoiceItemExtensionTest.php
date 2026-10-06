<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeInvoiceItemExtension;

it('defaults to a fake key, no validation errors and no annotation lines', function () {
    $extension = new FakeInvoiceItemExtension;

    expect($extension->key())->toBe('fake')
        ->and($extension->validate(['ids' => [1]], 5))->toBe([])
        ->and($extension->annotate(42, ['ids' => [1]]))->toBe([]);
});

it('allows configuring the key via the constructor', function () {
    $extension = new FakeInvoiceItemExtension('customer-assets');

    expect($extension->key())->toBe('customer-assets');
});

it('records every validate call', function () {
    $extension = new FakeInvoiceItemExtension;

    $extension->validate(['ids' => [1, 2]], 5);
    $extension->validate(['ids' => []], null);

    expect($extension->validateCalls)->toBe([
        ['extras' => ['ids' => [1, 2]], 'contactId' => 5],
        ['extras' => ['ids' => []], 'contactId' => null],
    ]);
});

it('returns configured validation errors', function () {
    $extension = new FakeInvoiceItemExtension;
    $extension->withValidationErrors(['ids.0' => 'Unbekanntes Objekt.']);

    expect($extension->validate(['ids' => [999]], 5))->toBe(['ids.0' => 'Unbekanntes Objekt.']);
});

it('records every afterItemsSynced call', function () {
    $extension = new FakeInvoiceItemExtension;

    $extension->afterItemsSynced(10, 5, [
        ['id' => 1, 'extras' => ['ids' => [1]]],
        ['id' => 2, 'extras' => ['ids' => []]],
    ]);

    expect($extension->afterItemsSyncedCalls)->toBe([
        ['invoiceId' => 10, 'contactId' => 5, 'items' => [
            ['id' => 1, 'extras' => ['ids' => [1]]],
            ['id' => 2, 'extras' => ['ids' => []]],
        ]],
    ]);
});

it('records every annotate call and returns configured lines', function () {
    $extension = new FakeInvoiceItemExtension;
    $extension->withAnnotationLines(['· example.de']);

    $lines = $extension->annotate(42, ['ids' => [1]]);

    expect($lines)->toBe(['· example.de'])
        ->and($extension->annotateCalls)->toBe([
            ['invoiceItemId' => 42, 'extras' => ['ids' => [1]]],
        ]);
});

it('records every duplicate call and returns the extras unchanged by default', function () {
    $extension = new FakeInvoiceItemExtension;

    $result = $extension->duplicate(['ids' => [1, 2], 'show' => true]);

    expect($result)->toBe(['ids' => [1, 2], 'show' => true])
        ->and($extension->duplicateCalls)->toBe([
            ['ids' => [1, 2], 'show' => true],
        ]);
});
