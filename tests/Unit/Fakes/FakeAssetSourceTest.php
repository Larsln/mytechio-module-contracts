<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Assets\AssetActionRef;
use MyTechIO\Contracts\Assets\AssetSource;
use MyTechIO\Contracts\Assets\AssetSourceDefaults;
use MyTechIO\Contracts\Assets\AssetSuggestion;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeAssetSource;

it('has no capabilities by default and throws on actions', function () {
    $source = new FakeAssetSource;

    expect($source->capabilities())->toBe([]);

    $source->setAutorenew('a.example', false, new ActorRef(1, 'Admin'));
})->throws(ContractException::class, 'Aktion wird von dieser Quelle nicht unterstützt.');

it('records supported actions and returns the configured state', function () {
    $actor = new ActorRef(1, 'Admin');
    $source = (new FakeAssetSource(key: 'domainrobot', capabilities: ['autorenew', 'owner_change', 'transfer_out']))
        ->actionState('queued');

    $ref = $source->setAutorenew('a.example', false, $actor);
    $source->transferToCompany('a.example', $actor);
    $source->releaseTransfer('a.example', $actor);

    expect($ref->state)->toBe('queued')
        ->and($ref->sourceKey)->toBe('domainrobot')
        ->and($ref->jobReference)->not->toBeNull()
        ->and($source->calls())->toHaveCount(3)
        ->and($source->calls()[0]['enabled'])->toBeFalse()
        ->and($source->calls()[2]['action'])->toBe('release_transfer');
});

it('suggests by type and query', function () {
    $source = new FakeAssetSource;
    $source->seedSuggestion(new AssetSuggestion('fake', 'domain', 'a.example', 'a.example'));
    $source->seedSuggestion(new AssetSuggestion('fake', 'domain', 'b.example', 'b.example'));

    expect($source->suggest('domain', 'b', 20))->toHaveCount(1)
        ->and($source->suggest('license', '', 20))->toBe([]);
});

it('gives sources default behavior through the trait', function () {
    $source = new class implements AssetSource
    {
        use AssetSourceDefaults;

        public function sourceKey(): string
        {
            return 'x';
        }

        public function assetTypes(): array
        {
            return [];
        }

        public function suggest(string $type, string $query, int $limit = 20): array
        {
            return [];
        }
    };

    expect($source->capabilities())->toBe([]);

    $source->releaseTransfer('x', new ActorRef(null, 'job'));
})->throws(ContractException::class);

it('validates the action state', function () {
    expect((new AssetActionRef('s', 'e', 'set_autorenew', null, 'done'))->toArray()['state'])->toBe('done');

    new AssetActionRef('s', 'e', 'set_autorenew', null, 'running');
})->throws(ContractException::class);
