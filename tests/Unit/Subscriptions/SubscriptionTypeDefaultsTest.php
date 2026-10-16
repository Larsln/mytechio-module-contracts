<?php

declare(strict_types=1);

use MyTechIO\Contracts\Subscriptions\SubscriptionData;
use MyTechIO\Contracts\Subscriptions\SubscriptionStatus;
use MyTechIO\Contracts\Subscriptions\SubscriptionType;
use MyTechIO\Contracts\Subscriptions\SubscriptionTypeDefaults;

it('gives types no input component and no-op hooks through the trait', function () {
    $type = new class implements SubscriptionType
    {
        use SubscriptionTypeDefaults;

        public function key(): string
        {
            return 'generic';
        }

        public function label(): string
        {
            return 'Allgemein';
        }

        public function schemaVersion(): int
        {
            return 1;
        }

        public function fields(): array
        {
            return [];
        }
    };
    $subscription = new SubscriptionData(1, 2, 'generic', 'Wartung', SubscriptionStatus::Active, null, null, '1', '2026-01-01');

    $type->created($subscription);
    $type->cancelled($subscription);
    $type->ended($subscription);

    expect($type->inputComponent())->toBeNull()
        ->and($type->singleObject())->toBeFalse();
});
