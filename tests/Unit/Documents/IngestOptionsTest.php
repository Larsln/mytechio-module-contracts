<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\IngestOptions;

it('defaults actor id and metadata', function () {
    $options = new IngestOptions;

    expect($options->actorId)->toBeNull()
        ->and($options->metadata)->toBe([]);
});

it('converts to an array with snake_case keys', function () {
    $options = new IngestOptions(actorId: 3, metadata: ['paperless_document_id' => 99]);

    expect($options->toArray())->toBe([
        'actor_id' => 3,
        'metadata' => ['paperless_document_id' => 99],
    ]);
});
