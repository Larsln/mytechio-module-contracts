<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Subscriptions\SubscriptionSource;
use MyTechIO\Contracts\Subscriptions\SubscriptionSourceDefaults;

function sourceWithDefaults(): SubscriptionSource
{
    return new class implements SubscriptionSource
    {
        use SubscriptionSourceDefaults;

        public function sourceKey(): string
        {
            return 'x';
        }

        public function subscriptionTypes(): array
        {
            return [];
        }

        public function suggest(string $type, string $query, int $limit = 20): array
        {
            return [];
        }
    };
}

it('offers no capabilities by default', function () {
    expect(sourceWithDefaults()->capabilities())->toBe([]);
});

it('throws on every action by default', function (string $action) {
    $source = sourceWithDefaults();
    $actor = new ActorRef(null, 'job');

    match ($action) {
        'setAutorenew' => $source->setAutorenew('x', true, $actor),
        'transferToCompany' => $source->transferToCompany('x', $actor),
        'releaseTransfer' => $source->releaseTransfer('x', $actor),
    };
})->with(['setAutorenew', 'transferToCompany', 'releaseTransfer'])
    ->throws(ContractException::class, 'Aktion wird von dieser Quelle nicht unterstützt.');
