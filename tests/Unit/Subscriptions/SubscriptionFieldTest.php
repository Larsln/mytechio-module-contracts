<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Subscriptions\SubscriptionField;
use MyTechIO\Contracts\Subscriptions\SubscriptionFieldKind;

it('defaults to an optional field without constraints', function () {
    $field = new SubscriptionField('registrar', 'Registrar', SubscriptionFieldKind::Text);

    expect($field->required)->toBeFalse()
        ->and($field->options)->toBe([])
        ->and($field->maxLength)->toBeNull()
        ->and($field->pattern)->toBeNull()
        ->and($field->min)->toBeNull()
        ->and($field->max)->toBeNull()
        ->and($field->listColumn)->toBeFalse()
        ->and($field->help)->toBeNull();
});

it('converts a text field to an array with snake_case keys', function () {
    $field = new SubscriptionField(
        key: 'domain_name',
        label: 'Domain',
        kind: SubscriptionFieldKind::Text,
        required: true,
        maxLength: 253,
        pattern: '/^[a-z0-9.-]+$/',
        listColumn: true,
        help: 'Ohne Protokoll',
    );

    expect($field->toArray())->toBe([
        'key' => 'domain_name',
        'label' => 'Domain',
        'kind' => 'text',
        'required' => true,
        'options' => [],
        'max_length' => 253,
        'pattern' => '/^[a-z0-9.-]+$/',
        'min' => null,
        'max' => null,
        'list_column' => true,
        'help' => 'Ohne Protokoll',
    ]);
});

it('converts select and number fields to an array', function () {
    $select = new SubscriptionField('tier', 'Stufe', SubscriptionFieldKind::Select, options: [
        ['value' => 'basic', 'label' => 'Basic'],
        ['value' => 'pro', 'label' => 'Pro'],
    ]);
    $number = new SubscriptionField('seats', 'Plätze', SubscriptionFieldKind::Number, min: '1', max: '500.5');

    expect($select->toArray()['options'])->toBe([
        ['value' => 'basic', 'label' => 'Basic'],
        ['value' => 'pro', 'label' => 'Pro'],
    ])
        ->and($number->toArray()['min'])->toBe('1')
        ->and($number->toArray()['max'])->toBe('500.5');
});

it('accepts the allowed constraints per kind', function () {
    $secret = new SubscriptionField('auth_code', 'Auth-Code', SubscriptionFieldKind::Secret, maxLength: 64);
    $date = new SubscriptionField('expires_on', 'Läuft ab', SubscriptionFieldKind::Date);
    $bool = new SubscriptionField('locked', 'Gesperrt', SubscriptionFieldKind::Bool);

    expect($secret->maxLength)->toBe(64)
        ->and($date->kind)->toBe(SubscriptionFieldKind::Date)
        ->and($bool->kind)->toBe(SubscriptionFieldKind::Bool);
});

it('rejects an invalid key', function (string $key) {
    new SubscriptionField($key, 'Label', SubscriptionFieldKind::Text);
})->with([
    'empty' => [''],
    'starts with digit' => ['1abc'],
    'starts with underscore' => ['_abc'],
    'uppercase' => ['Domain'],
    'dash' => ['domain-name'],
    'space' => ['domain name'],
    'too long' => [str_repeat('a', 65)],
])->throws(ContractException::class, 'key must match');

it('accepts a key with the maximum length', function () {
    expect((new SubscriptionField(str_repeat('a', 64), 'Label', SubscriptionFieldKind::Text))->key)->toHaveLength(64);
});

it('requires options for a select field', function () {
    new SubscriptionField('tier', 'Stufe', SubscriptionFieldKind::Select);
})->throws(ContractException::class, 'options must not be empty');

it('rejects options on a field that is not a select', function () {
    new SubscriptionField('tier', 'Stufe', SubscriptionFieldKind::Text, options: [['value' => 'a', 'label' => 'A']]);
})->throws(ContractException::class, 'only allowed for a select');

it('rejects malformed options', function (array $options) {
    new SubscriptionField('tier', 'Stufe', SubscriptionFieldKind::Select, options: $options);
})->with([
    'not an array' => [['basic']],
    'missing label' => [[['value' => 'basic']]],
    'missing value' => [[['label' => 'Basic']]],
    'non-string value' => [[['value' => 1, 'label' => 'Basic']]],
    'non-string label' => [[['value' => 'basic', 'label' => null]]],
])->throws(ContractException::class, 'string value and a string label');

it('restricts maxLength to text and secret fields and requires at least 1', function (SubscriptionFieldKind $kind, int $maxLength) {
    new SubscriptionField('x', 'X', $kind, maxLength: $maxLength);
})->with([
    'number' => [SubscriptionFieldKind::Number, 10],
    'date' => [SubscriptionFieldKind::Date, 10],
    'bool' => [SubscriptionFieldKind::Bool, 10],
    'zero' => [SubscriptionFieldKind::Text, 0],
    'negative' => [SubscriptionFieldKind::Secret, -1],
])->throws(ContractException::class, 'maxLength');

it('restricts pattern to text fields', function () {
    new SubscriptionField('x', 'X', SubscriptionFieldKind::Secret, pattern: '/^a$/');
})->throws(ContractException::class, 'pattern is only allowed for text');

it('rejects a pattern that is not a valid regular expression', function (string $pattern) {
    new SubscriptionField('x', 'X', SubscriptionFieldKind::Text, pattern: $pattern);
})->with([
    'no delimiters' => ['abc'],
    'unbalanced group' => ['/(abc/'],
    'empty' => [''],
])->throws(ContractException::class, 'pattern is not a valid regular expression');

it('restricts min and max to number fields', function (?string $min, ?string $max) {
    new SubscriptionField('x', 'X', SubscriptionFieldKind::Text, min: $min, max: $max);
})->with([
    'min' => ['1', null],
    'max' => [null, '9'],
])->throws(ContractException::class, 'only allowed for number');

it('rejects min and max that are not decimal strings', function (?string $min, ?string $max) {
    new SubscriptionField('x', 'X', SubscriptionFieldKind::Number, min: $min, max: $max);
})->with([
    'min comma' => ['1,5', null],
    'max letters' => [null, 'ten'],
    'max too many decimals' => ['1', '9.00001'],
])->throws(ContractException::class);
