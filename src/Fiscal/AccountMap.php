<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * Maps the booking roles of the core (`AccountRole`) to ledger account
 * numbers, and names the account blocks and the protected core accounts of the
 * profile's chart.
 *
 * Rate keys are normalized decimal strings without trailing zeros (`19`, `7`,
 * `20.5`); a profile may answer a role for any rate with one account.
 */
interface AccountMap
{
    /**
     * The account number for a role.
     *
     * @throws AccountNotMappedException when the profile maps nothing for the role, rate and scenario
     */
    public function number(AccountRole $role, ?string $rateKey = null, ?string $scenario = null): string;

    /**
     * Whether `number()` would answer.
     */
    public function has(AccountRole $role, ?string $rateKey = null, ?string $scenario = null): bool;

    /**
     * Every VAT account of the map (output, input, self-assessment), to find
     * the bookings a VAT return has to look at.
     *
     * @return list<string>
     */
    public function vatAccounts(): array;

    /**
     * @return list<AccountRange>
     */
    public function ranges(): array;

    /**
     * @throws AccountNotMappedException
     */
    public function range(AccountRangeKind $kind): AccountRange;

    /**
     * Numbers of the accounts the chart protects: they keep number and type forever.
     *
     * @return list<string>
     */
    public function coreAccounts(): array;
}
