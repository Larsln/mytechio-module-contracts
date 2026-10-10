<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Subscriptions\NewSubscription;

it('defaults to quantity 1 and null for everything optional', function () {
    $subscription = new NewSubscription(contactId: 2, type: 'domain', label: 'example.de');

    expect($subscription->quantity)->toBe('1')
        ->and($subscription->articleId)->toBeNull()
        ->and($subscription->priceOptionId)->toBeNull()
        ->and($subscription->startsOn)->toBeNull()
        ->and($subscription->source)->toBeNull()
        ->and($subscription->externalId)->toBeNull()
        ->and($subscription->providerName)->toBeNull()
        ->and($subscription->renewsOn)->toBeNull()
        ->and($subscription->autorenew)->toBeNull()
        ->and($subscription->attributes)->toBe([]);
});

it('converts to an array with snake_case keys', function () {
    $subscription = new NewSubscription(contactId: 2, type: 'domain', label: 'example.de');

    expect($subscription->toArray())->toBe([
        'contact_id' => 2,
        'type' => 'domain',
        'label' => 'example.de',
        'article_id' => null,
        'price_option_id' => null,
        'quantity' => '1',
        'starts_on' => null,
        'source' => null,
        'external_id' => null,
        'provider_name' => null,
        'renews_on' => null,
        'autorenew' => null,
        'attributes' => [],
    ]);
});

it('converts a fully specified subscription to an array', function () {
    $subscription = new NewSubscription(
        contactId: 2,
        type: 'domain',
        label: 'example.de',
        articleId: 9,
        priceOptionId: 4,
        quantity: '0.5',
        startsOn: '2026-11-01',
        source: 'domainrobot',
        externalId: 'example.de',
        renewsOn: '2027-11-01',
        autorenew: true,
        attributes: ['registrar' => 'kyberio'],
    );

    expect($subscription->toArray())->toBe([
        'contact_id' => 2,
        'type' => 'domain',
        'label' => 'example.de',
        'article_id' => 9,
        'price_option_id' => 4,
        'quantity' => '0.5',
        'starts_on' => '2026-11-01',
        'source' => 'domainrobot',
        'external_id' => 'example.de',
        'provider_name' => null,
        'renews_on' => '2027-11-01',
        'autorenew' => true,
        'attributes' => ['registrar' => 'kyberio'],
    ]);
});

it('accepts a price option together with an article', function () {
    $subscription = new NewSubscription(contactId: 2, type: 'generic', label: 'Wartung', articleId: 9, priceOptionId: 4);

    expect($subscription->priceOptionId)->toBe(4);
});

it('rejects a price option without an article', function () {
    new NewSubscription(contactId: 2, type: 'generic', label: 'Wartung', priceOptionId: 4);
})->throws(ContractException::class, 'priceOptionId requires articleId.');

it('rejects a quantity that is zero, negative or not a decimal string', function (string $quantity) {
    new NewSubscription(contactId: 2, type: 'generic', label: 'Wartung', quantity: $quantity);
})->with([
    'zero' => ['0'],
    'zero with decimals' => ['0.0'],
    'zero with four decimals' => ['0.0000'],
    'padded zero' => ['00'],
    'negative' => ['-1'],
    'negative decimal' => ['-0.5'],
    'comma' => ['1,5'],
    'too many decimals' => ['1.00001'],
    'empty' => [''],
])->throws(ContractException::class);

it('accepts the smallest positive quantity', function () {
    expect((new NewSubscription(contactId: 2, type: 'generic', label: 'Wartung', quantity: '0.0001'))->quantity)->toBe('0.0001');
});

it('rejects malformed dates', function (string $field, string $value) {
    new NewSubscription(...['contactId' => 2, 'type' => 'generic', 'label' => 'Wartung', $field => $value]);
})->with([
    'startsOn' => ['startsOn', '01.11.2026'],
    'renewsOn' => ['renewsOn', '2026-02-30'],
])->throws(ContractException::class);

it('rejects a source without an external id and vice versa', function (?string $source, ?string $externalId) {
    new NewSubscription(contactId: 2, type: 'domain', label: 'example.de', source: $source, externalId: $externalId);
})->with([
    'source only' => ['domainrobot', null],
    'external id only' => [null, 'example.de'],
])->throws(ContractException::class);
