<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

use MyTechIO\Contracts\ContractException;

/**
 * The chart of accounts: lookups, the filtered list and the maintenance of
 * custom accounts.
 *
 * Accounts are never deleted, only deactivated, so historic bookings stay
 * resolvable; core accounts (the seeded chart) are protected — number and
 * type cannot change. Rule violations (a duplicate number, a core account
 * change, an unknown parent, …) surface as `ContractException` with a German
 * message, an unknown ID as `AccountNotFoundException`. Every write action
 * fires its domain event (`AccountCreated`, `AccountUpdated`,
 * `AccountDeactivated`, `AccountActivated`) after the commit.
 * `Journal::findAccount()` returns the same data as `find()`.
 */
interface Accounts
{
    public function find(string $number): ?AccountInfo;

    public function findById(int $id): ?AccountInfo;

    /**
     * Accounts ordered by number. `$page` starts at 1.
     */
    public function list(AccountFilter $filter = new AccountFilter, int $page = 1, int $perPage = 50): AccountPage;

    /**
     * @throws ContractException if the number is taken or a reference does not exist.
     */
    public function create(NewAccount $account, ActorRef $actor): AccountInfo;

    /**
     * @throws AccountNotFoundException
     * @throws ContractException if the number is taken or a core account's number or type would change.
     */
    public function update(int $id, AccountChanges $changes, ActorRef $actor): AccountInfo;

    /**
     * The account stops accepting bookings but keeps resolving in historic
     * entries and reports.
     *
     * @throws AccountNotFoundException
     */
    public function deactivate(int $id, ActorRef $actor): AccountInfo;

    /**
     * @throws AccountNotFoundException
     */
    public function activate(int $id, ActorRef $actor): AccountInfo;
}
