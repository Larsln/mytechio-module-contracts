<?php

declare(strict_types=1);

use MyTechIO\Contracts\Subscriptions\PackageComponentInput;

it('defaults to no attributes', function () {
    $input = new PackageComponentInput(9);

    expect($input->attributes)->toBe([])->and($input->source)->toBeNull()->and($input->externalId)->toBeNull();
});

it('converts to an array with snake_case keys', function () {
    expect((new PackageComponentInput(9, ['fqdn' => 'example.de']))->toArray())->toBe([
        'article_id' => 9,
        'attributes' => ['fqdn' => 'example.de'],
        'source' => null,
        'external_id' => null,
    ]);
});

it('carries the source binding of a component', function () {
    $input = new PackageComponentInput(9, ['fqdn' => 'example.de'], 'fakerobot', 'example.de');

    expect($input->source)->toBe('fakerobot')->and($input->externalId)->toBe('example.de')
        ->and($input->toArray())->toMatchArray(['source' => 'fakerobot', 'external_id' => 'example.de']);
});
