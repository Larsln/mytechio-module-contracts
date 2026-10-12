<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\AccountChanges;
use MyTechIO\Contracts\Accounting\AccountFilter;
use MyTechIO\Contracts\Accounting\AccountInfo;
use MyTechIO\Contracts\Accounting\AccountNotFoundException;
use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Accounting\NewAccount;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeAccounts;

function accountsFake(): FakeAccounts
{
    $accounts = new FakeAccounts;
    $accounts->seed(new AccountInfo('0650', 'Büroeinrichtung', 'asset', isCore: true));
    $accounts->seed(new AccountInfo('1200', 'Bank', 'asset', isCore: true));
    $accounts->seed(new AccountInfo('6260', 'Sofortabschreibung GWG', 'expense', isCore: true, isCommonlyUsed: false));

    return $accounts;
}

function accountActor(): ActorRef
{
    return new ActorRef(1, 'Tester');
}

it('looks accounts up by number and ID', function () {
    $accounts = accountsFake();

    expect($accounts->find('1200')?->id)->toBe(2)
        ->and($accounts->findById(1)?->number)->toBe('0650')
        ->and($accounts->find('9999'))->toBeNull()
        ->and($accounts->findById(99))->toBeNull();
});

it('lists by number with the core filters', function () {
    $accounts = accountsFake();

    expect(array_column($accounts->list()->items, 'number'))->toBe(['0650', '1200', '6260'])
        ->and(array_column($accounts->list(new AccountFilter(numberTo: '0999'))->items, 'number'))->toBe(['0650'])
        ->and(array_column($accounts->list(new AccountFilter(type: 'expense'))->items, 'number'))->toBe(['6260'])
        ->and(array_column($accounts->list(new AccountFilter(search: 'bank'))->items, 'number'))->toBe(['1200'])
        ->and(array_column($accounts->list(new AccountFilter(commonlyUsedOnly: true))->items, 'number'))->toBe(['0650', '1200'])
        ->and($accounts->list(new AccountFilter, 2, 2)->items)->toHaveCount(1)
        ->and($accounts->list(new AccountFilter, 1, 2)->lastPage)->toBe(2);
});

it('creates custom accounts with unique numbers and fires AccountCreated', function () {
    $accounts = accountsFake();
    $created = $accounts->create(new NewAccount('0651', 'Eigenes Anlagekonto', 'asset'), accountActor());

    expect($created->isCore)->toBeFalse()
        ->and($created->isActive)->toBeTrue()
        ->and($accounts->events()[0]['event'])->toBe('AccountCreated');

    expect(fn () => $accounts->create(new NewAccount('0651', 'Doppelt', 'asset'), accountActor()))->toThrow(ContractException::class, 'bereits vergeben');
});

it('protects number and type of core accounts', function () {
    $accounts = accountsFake();

    expect(fn () => $accounts->update(1, new AccountChanges('0651', 'Büroeinrichtung', 'asset'), accountActor()))
        ->toThrow(ContractException::class, 'Kernkontos');

    $updated = $accounts->update(1, new AccountChanges('0650', 'Anderer Name', 'asset', nameLong: 'Lang'), accountActor());

    expect($updated->name)->toBe('Büroeinrichtung')
        ->and($updated->nameLong)->toBe('Lang')
        ->and($accounts->events()[0]['payload']['changes'])->toHaveKey('name_long');
});

it('deactivates and activates instead of deleting', function () {
    $accounts = accountsFake();

    expect($accounts->deactivate(1, accountActor())->isActive)->toBeFalse()
        ->and(array_column($accounts->list()->items, 'number'))->toBe(['1200', '6260'])
        ->and(array_column($accounts->list(new AccountFilter(active: null))->items, 'number'))->toBe(['0650', '1200', '6260'])
        ->and($accounts->activate(1, accountActor())->isActive)->toBeTrue()
        ->and(array_column($accounts->events(), 'event'))->toBe(['AccountDeactivated', 'AccountActivated']);
});

it('throws for an unknown ID on writes', function () {
    accountsFake()->deactivate(99, accountActor());
})->throws(AccountNotFoundException::class);
