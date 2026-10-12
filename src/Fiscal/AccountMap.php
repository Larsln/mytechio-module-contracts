<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * Maps the booking roles of the core (`AccountRole`) to ledger account
 * numbers, and names the account blocks and the protected core accounts of the
 * chart.
 *
 * The core binds this contract: `app(AccountMap::class)` is the
 * organization's own mapping, seeded from the reference mapping of the fiscal
 * profile's chart (`ChartOfAccounts::roles()`) and maintained by the
 * organization afterwards. Modules ask the container-bound `AccountMap`, never
 * `FiscalProfile::accounts()`.
 *
 * Rate keys are normalized decimal strings without trailing zeros (`19`, `7`,
 * `20.5`); a profile may answer a role for any rate with one account.
 */
interface AccountMap
{
    /**
     * The account number for a role.
     *
     * @throws AccountNotMappedException when the organization maps nothing for the role, rate and scenario
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
     * @throws AccountRangeNotMappedException when the organization has not set the block
     */
    public function range(AccountRangeKind $kind): AccountRange;

    /**
     * Numbers of the organization's protected accounts (the seeded ones): they
     * keep number and type forever.
     *
     * @return list<string>
     */
    public function coreAccounts(): array;
}
