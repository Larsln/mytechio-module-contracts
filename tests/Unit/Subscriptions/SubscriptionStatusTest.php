<?php

declare(strict_types=1);

use MyTechIO\Contracts\Subscriptions\SubscriptionStatus;

it('is a string-backed enum with the four lifecycle states', function () {
    expect(SubscriptionStatus::Active->value)->toBe('active')
        ->and(SubscriptionStatus::Paused->value)->toBe('paused')
        ->and(SubscriptionStatus::Cancelled->value)->toBe('cancelled')
        ->and(SubscriptionStatus::Ended->value)->toBe('ended')
        ->and(SubscriptionStatus::from('cancelled'))->toBe(SubscriptionStatus::Cancelled)
        ->and(SubscriptionStatus::cases())->toHaveCount(4);
});

it('provides German labels', function () {
    expect(SubscriptionStatus::Active->label())->toBe('Aktiv')
        ->and(SubscriptionStatus::Paused->label())->toBe('Pausiert')
        ->and(SubscriptionStatus::Cancelled->label())->toBe('Gekündigt')
        ->and(SubscriptionStatus::Ended->label())->toBe('Beendet');
});
