<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\DocumentMeta;

it('defaults title, tags and options', function () {
    $meta = new DocumentMeta(ownerType: 'invoice', ownerId: 42, kind: 'invoice_pdf');

    expect($meta->title)->toBeNull()
        ->and($meta->tags)->toBe([])
        ->and($meta->options)->toBe([]);
});

it('converts to an array with snake_case keys', function () {
    $meta = new DocumentMeta(
        ownerType: 'invoice',
        ownerId: 42,
        kind: 'invoice_pdf',
        title: 'Rechnung 2026-0001',
        tags: ['final'],
        options: ['skip_paperless_notify' => true],
    );

    expect($meta->toArray())->toBe([
        'owner_type' => 'invoice',
        'owner_id' => 42,
        'kind' => 'invoice_pdf',
        'title' => 'Rechnung 2026-0001',
        'tags' => ['final'],
        'options' => ['skip_paperless_notify' => true],
    ]);
});
