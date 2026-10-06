<?php

declare(strict_types=1);

use MyTechIO\Contracts\Assets\AssetSuggestion;

it('defaults attributes to an empty array', function () {
    $suggestion = new AssetSuggestion(source: 'domainrobot', type: 'domain', externalId: 'example.de', label: 'example.de');

    expect($suggestion->attributes)->toBe([]);
});

it('converts to an array with snake_case keys', function () {
    $suggestion = new AssetSuggestion(
        source: 'domainrobot',
        type: 'domain',
        externalId: 'example.de',
        label: 'example.de',
        attributes: ['status' => 'active'],
    );

    expect($suggestion->toArray())->toBe([
        'source' => 'domainrobot',
        'type' => 'domain',
        'external_id' => 'example.de',
        'label' => 'example.de',
        'attributes' => ['status' => 'active'],
    ]);
});
