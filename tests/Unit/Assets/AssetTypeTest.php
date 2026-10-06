<?php

declare(strict_types=1);

use MyTechIO\Contracts\Assets\AssetType;

it('defines the known asset types', function () {
    expect(AssetType::Domain)->toBe('domain')
        ->and(AssetType::License)->toBe('license')
        ->and(AssetType::Hosting)->toBe('hosting')
        ->and(AssetType::Certificate)->toBe('certificate')
        ->and(AssetType::Other)->toBe('other');
});

it('cannot be instantiated', function () {
    expect(fn () => new AssetType)->toThrow(Error::class);
});
