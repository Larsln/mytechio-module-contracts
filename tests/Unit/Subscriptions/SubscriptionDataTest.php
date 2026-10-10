<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Subscriptions\SubscriptionData;
use MyTechIO\Contracts\Subscriptions\SubscriptionStatus;

/**
 * @param  array<string, mixed>  $overrides
 */
function subscriptionData(array $overrides = []): SubscriptionData
{
    return new SubscriptionData(...[
        'id' => 1,
        'contactId' => 2,
        'type' => 'domain',
        'label' => 'example.de',
        'status' => SubscriptionStatus::Active,
        'articleId' => 9,
        'priceOptionId' => 4,
        'quantity' => '1',
        'startsOn' => '2026-01-01',
        ...$overrides,
    ]);
}

it('defaults the optional fields to null and attributes to an empty array', function () {
    $subscription = subscriptionData();

    expect($subscription->endsOn)->toBeNull()
        ->and($subscription->cancelledOn)->toBeNull()
        ->and($subscription->billedUntil)->toBeNull()
        ->and($subscription->source)->toBeNull()
        ->and($subscription->externalId)->toBeNull()
        ->and($subscription->providerName)->toBeNull()
        ->and($subscription->renewsOn)->toBeNull()
        ->and($subscription->autorenew)->toBeNull()
        ->and($subscription->externalStatus)->toBeNull()
        ->and($subscription->attributes)->toBe([]);
});

it('converts to an array with snake_case keys and the status value', function () {
    $subscription = subscriptionData();

    expect($subscription->toArray())->toBe([
        'id' => 1,
        'contact_id' => 2,
        'type' => 'domain',
        'label' => 'example.de',
        'status' => 'active',
        'article_id' => 9,
        'price_option_id' => 4,
        'quantity' => '1',
        'starts_on' => '2026-01-01',
        'ends_on' => null,
        'cancelled_on' => null,
        'billed_until' => null,
        'source' => null,
        'external_id' => null,
        'provider_name' => null,
        'renews_on' => null,
        'autorenew' => null,
        'external_status' => null,
        'attributes' => [],
    ]);
});

it('converts a cancelled, sourced subscription with attributes to an array', function () {
    $subscription = subscriptionData([
        'status' => SubscriptionStatus::Cancelled,
        'articleId' => null,
        'priceOptionId' => null,
        'quantity' => '2.5000',
        'endsOn' => '2026-12-31',
        'cancelledOn' => '2026-10-10',
        'billedUntil' => '2026-12-31',
        'source' => 'domainrobot',
        'externalId' => 'example.de',
        'renewsOn' => '2027-01-01',
        'autorenew' => false,
        'externalStatus' => 'active',
        'attributes' => ['auth_code' => 'secret'],
    ]);

    expect($subscription->toArray())->toBe([
        'id' => 1,
        'contact_id' => 2,
        'type' => 'domain',
        'label' => 'example.de',
        'status' => 'cancelled',
        'article_id' => null,
        'price_option_id' => null,
        'quantity' => '2.5000',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-12-31',
        'cancelled_on' => '2026-10-10',
        'billed_until' => '2026-12-31',
        'source' => 'domainrobot',
        'external_id' => 'example.de',
        'provider_name' => null,
        'renews_on' => '2027-01-01',
        'autorenew' => false,
        'external_status' => 'active',
        'attributes' => ['auth_code' => 'secret'],
    ]);
});

it('accepts a provider name without a source', function () {
    expect(subscriptionData(['providerName' => 'IONOS'])->providerName)->toBe('IONOS');
});

it('rejects a malformed quantity', function (string $quantity) {
    subscriptionData(['quantity' => $quantity]);
})->with(['comma' => ['1,5'], 'too many decimals' => ['1.00001'], 'empty' => ['']])->throws(ContractException::class);

it('rejects malformed dates in every date field', function (string $field, string $value) {
    subscriptionData([$field => $value]);
})->with([
    'startsOn' => ['startsOn', '01.01.2026'],
    'endsOn' => ['endsOn', '2026-02-30'],
    'cancelledOn' => ['cancelledOn', '2026-1-1'],
    'billedUntil' => ['billedUntil', 'yesterday'],
    'renewsOn' => ['renewsOn', '2027-01-01T00:00:00+00:00'],
])->throws(ContractException::class);

it('rejects a source without an external id and vice versa', function (?string $source, ?string $externalId) {
    subscriptionData(['source' => $source, 'externalId' => $externalId]);
})->with([
    'source only' => ['domainrobot', null],
    'external id only' => [null, 'example.de'],
])->throws(ContractException::class);
