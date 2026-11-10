<?php

declare(strict_types=1);

use MyTechIO\Contracts\Subscriptions\PackageComponentInput;

it('defaults to no attributes', function () {
    expect((new PackageComponentInput(9))->attributes)->toBe([]);
});

it('converts to an array with snake_case keys', function () {
    expect((new PackageComponentInput(9, ['fqdn' => 'example.de']))->toArray())->toBe([
        'article_id' => 9,
        'attributes' => ['fqdn' => 'example.de'],
    ]);
});
