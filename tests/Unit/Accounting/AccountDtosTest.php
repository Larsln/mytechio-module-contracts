<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\AccountChanges;
use MyTechIO\Contracts\Accounting\AccountFilter;
use MyTechIO\Contracts\Accounting\AccountInfo;
use MyTechIO\Contracts\Accounting\AccountNotFoundException;
use MyTechIO\Contracts\Accounting\AccountPage;
use MyTechIO\Contracts\Accounting\NewAccount;
use MyTechIO\Contracts\ContractException;

it('filters active accounts by default', function () {
    $filter = new AccountFilter;

    expect($filter->active)->toBeTrue()
        ->and($filter->toArray())->toBe([
            'type' => null,
            'active' => true,
            'number_from' => null,
            'number_to' => null,
            'search' => null,
            'commonly_used_only' => false,
            'custom_only' => false,
        ]);
});

it('converts a page to arrays', function () {
    $page = new AccountPage([new AccountInfo('1200', 'Bank', 'asset', 1)], 51, 2, 50, 2);

    expect($page->toArray())->toMatchArray(['total' => 51, 'page' => 2, 'per_page' => 50, 'last_page' => 2])
        ->and($page->toArray()['items'][0]['number'])->toBe('1200');
});

it('converts account input to snake_case arrays', function () {
    $new = new NewAccount('4999', 'Sonstiges', 'revenue', nameLong: 'Sonstige Erlöse', parentId: 5);
    $changes = new AccountChanges('4999', 'Sonstiges', 'revenue', isCommonlyUsed: false);

    expect($new->toArray())->toMatchArray(['number' => '4999', 'name_long' => 'Sonstige Erlöse', 'parent_id' => 5, 'is_commonly_used' => true])
        ->and($changes->toArray())->toMatchArray(['type' => 'revenue', 'is_commonly_used' => false]);
});

it('names the unknown account in a German message', function () {
    $exception = new AccountNotFoundException(42);

    expect($exception)->toBeInstanceOf(ContractException::class)
        ->and($exception->id)->toBe(42)
        ->and($exception->getMessage())->toBe('Konto mit ID 42 wurde nicht gefunden.');
});
