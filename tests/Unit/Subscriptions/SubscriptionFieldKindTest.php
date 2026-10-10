<?php

declare(strict_types=1);

use MyTechIO\Contracts\Subscriptions\SubscriptionFieldKind;

it('is a string-backed enum with the six field kinds', function () {
    expect(SubscriptionFieldKind::Text->value)->toBe('text')
        ->and(SubscriptionFieldKind::Number->value)->toBe('number')
        ->and(SubscriptionFieldKind::Date->value)->toBe('date')
        ->and(SubscriptionFieldKind::Bool->value)->toBe('bool')
        ->and(SubscriptionFieldKind::Select->value)->toBe('select')
        ->and(SubscriptionFieldKind::Secret->value)->toBe('secret')
        ->and(SubscriptionFieldKind::from('secret'))->toBe(SubscriptionFieldKind::Secret)
        ->and(SubscriptionFieldKind::cases())->toHaveCount(6);
});
