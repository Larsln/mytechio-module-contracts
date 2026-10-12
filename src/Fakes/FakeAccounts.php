<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\AccountChanges;
use MyTechIO\Contracts\Accounting\AccountFilter;
use MyTechIO\Contracts\Accounting\AccountInfo;
use MyTechIO\Contracts\Accounting\AccountNotFoundException;
use MyTechIO\Contracts\Accounting\AccountPage;
use MyTechIO\Contracts\Accounting\Accounts;
use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Accounting\NewAccount;
use MyTechIO\Contracts\ContractException;

/**
 * Test double for `Accounts`: an in-memory chart of accounts with auto IDs,
 * the core's rules (unique numbers, core accounts keep number and type,
 * accounts are deactivated instead of deleted) and the same filters as the
 * core. `seed()` adds an account (a missing ID is assigned); `events()` lists
 * the domain events the real implementation would have fired, as
 * `['event' => name, 'payload' => array]`.
 */
final class FakeAccounts implements Accounts
{
    /**
     * @var array<int, AccountInfo>
     */
    private array $accounts = [];

    private int $nextId = 1;

    /**
     * @var list<array{event: string, payload: array<string, mixed>}>
     */
    private array $events = [];

    public function seed(AccountInfo $account): AccountInfo
    {
        $seeded = $account->id === null ? self::withId($account, $this->nextId) : $account;
        $this->accounts[(int) $seeded->id] = $seeded;
        $this->nextId = max($this->nextId, (int) $seeded->id + 1);

        return $seeded;
    }

    public function find(string $number): ?AccountInfo
    {
        foreach ($this->accounts as $account) {
            if ($account->number === $number) {
                return $account;
            }
        }

        return null;
    }

    public function findById(int $id): ?AccountInfo
    {
        return $this->accounts[$id] ?? null;
    }

    public function list(AccountFilter $filter = new AccountFilter, int $page = 1, int $perPage = 50): AccountPage
    {
        $matches = array_values(array_filter($this->accounts, fn (AccountInfo $account): bool => $this->matches($account, $filter)));
        usort($matches, static fn (AccountInfo $a, AccountInfo $b): int => strcmp($a->number, $b->number));

        $perPage = max(1, $perPage);
        $page = max(1, $page);

        return new AccountPage(
            items: array_slice($matches, ($page - 1) * $perPage, $perPage),
            total: count($matches),
            page: $page,
            perPage: $perPage,
            lastPage: max(1, (int) ceil(count($matches) / $perPage)),
        );
    }

    public function create(NewAccount $account, ActorRef $actor): AccountInfo
    {
        if ($this->find($account->number) !== null) {
            throw new ContractException('Die Kontonummer ist bereits vergeben.');
        }

        $created = $this->seed(new AccountInfo(
            number: $account->number,
            name: $account->name,
            type: $account->type,
            isActive: true,
            isCore: false,
            nameLong: $account->nameLong,
            category: $account->category,
            parentId: $account->parentId,
            defaultTaxCategoryId: $account->defaultTaxCategoryId,
            taxScenario: $account->taxScenario,
            isCommonlyUsed: $account->isCommonlyUsed,
        ));

        $this->events[] = ['event' => 'AccountCreated', 'payload' => ['account_id' => $created->id, 'number' => $created->number, 'name' => $created->name, 'type' => $created->type]];

        return $created;
    }

    public function update(int $id, AccountChanges $changes, ActorRef $actor): AccountInfo
    {
        $account = $this->get($id);
        $taken = $this->find($changes->number);

        if ($taken !== null && $taken->id !== $id) {
            throw new ContractException('Die Kontonummer ist bereits vergeben.');
        }

        if ($account->isCore && ($changes->number !== $account->number || $changes->type !== $account->type)) {
            throw new ContractException('Nummer und Typ eines Kernkontos können nicht geändert werden.');
        }

        $updated = new AccountInfo(
            number: $account->isCore ? $account->number : $changes->number,
            name: $account->isCore ? $account->name : $changes->name,
            type: $account->isCore ? $account->type : $changes->type,
            id: $id,
            isActive: $account->isActive,
            isCore: $account->isCore,
            nameLong: $changes->nameLong,
            category: $account->isCore ? $account->category : $changes->category,
            parentId: $changes->parentId,
            defaultTaxCategoryId: $changes->defaultTaxCategoryId,
            taxScenario: $changes->taxScenario,
            isCommonlyUsed: $changes->isCommonlyUsed,
        );

        $this->accounts[$id] = $updated;
        $this->events[] = ['event' => 'AccountUpdated', 'payload' => ['account_id' => $id, 'number' => $updated->number, 'changes' => self::changed($account, $updated)]];

        return $updated;
    }

    public function deactivate(int $id, ActorRef $actor): AccountInfo
    {
        $deactivated = $this->setActive($this->get($id), false);
        $this->events[] = ['event' => 'AccountDeactivated', 'payload' => ['account_id' => $id, 'number' => $deactivated->number]];

        return $deactivated;
    }

    public function activate(int $id, ActorRef $actor): AccountInfo
    {
        $activated = $this->setActive($this->get($id), true);
        $this->events[] = ['event' => 'AccountActivated', 'payload' => ['account_id' => $id, 'number' => $activated->number]];

        return $activated;
    }

    /**
     * @return list<array{event: string, payload: array<string, mixed>}>
     */
    public function events(): array
    {
        return $this->events;
    }

    private function get(int $id): AccountInfo
    {
        return $this->accounts[$id] ?? throw new AccountNotFoundException($id);
    }

    private function setActive(AccountInfo $account, bool $active): AccountInfo
    {
        $changed = new AccountInfo(
            $account->number,
            $account->name,
            $account->type,
            $account->id,
            $active,
            $account->isCore,
            $account->nameLong,
            $account->category,
            $account->parentId,
            $account->defaultTaxCategoryId,
            $account->taxScenario,
            $account->isCommonlyUsed,
        );
        $this->accounts[(int) $account->id] = $changed;

        return $changed;
    }

    private function matches(AccountInfo $account, AccountFilter $filter): bool
    {
        if ($filter->type !== null && $account->type !== $filter->type) {
            return false;
        }

        if ($filter->active !== null && $account->isActive !== $filter->active) {
            return false;
        }

        if ($filter->numberFrom !== null && strcmp($account->number, $filter->numberFrom) < 0) {
            return false;
        }

        if ($filter->numberTo !== null && strcmp($account->number, $filter->numberTo) > 0) {
            return false;
        }

        if ($filter->commonlyUsedOnly && ! $account->isCommonlyUsed) {
            return false;
        }

        if ($filter->customOnly && $account->isCore) {
            return false;
        }

        if ($filter->search !== null && $filter->search !== '') {
            $haystack = mb_strtolower($account->number.' '.$account->name.' '.($account->nameLong ?? ''));

            return str_contains($haystack, mb_strtolower($filter->search));
        }

        return true;
    }

    /**
     * @return array<string, array{from: mixed, to: mixed}>
     */
    private static function changed(AccountInfo $before, AccountInfo $after): array
    {
        $changes = [];

        foreach ($after->toArray() as $field => $value) {
            $old = $before->toArray()[$field];

            if ($old !== $value) {
                $changes[$field] = ['from' => $old, 'to' => $value];
            }
        }

        return $changes;
    }

    private static function withId(AccountInfo $account, int $id): AccountInfo
    {
        return new AccountInfo(
            $account->number,
            $account->name,
            $account->type,
            $id,
            $account->isActive,
            $account->isCore,
            $account->nameLong,
            $account->category,
            $account->parentId,
            $account->defaultTaxCategoryId,
            $account->taxScenario,
            $account->isCommonlyUsed,
        );
    }
}
