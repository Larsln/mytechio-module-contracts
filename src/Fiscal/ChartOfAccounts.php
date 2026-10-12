<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The chart of accounts a fiscal profile brings: the seed data of the
 * `accounts` table, its links to tax scenarios and tax categories, and the
 * reference mapping of roles and ranges. The profile only provides the
 * template: after setup the accounts, the role mapping and the ranges are the
 * organization's data and user-maintainable.
 */
interface ChartOfAccounts
{
    /**
     * Every account of the chart; core accounts first-class (`isCore`), the
     * optional extended chart (not protected) after them.
     *
     * @return list<ChartAccount>
     */
    public function all(): array;

    public function find(string $number): ?ChartAccount;

    /**
     * The automatic accounts of the outgoing scenarios and the bound expense
     * accounts of the incoming scenarios (see `AutoBookingLink`).
     *
     * @return list<AutoBookingLink>
     */
    public function autoBookingLinks(): array;

    /**
     * The links between tax categories and accounts.
     *
     * @return list<TaxCategoryLink>
     */
    public function taxCategoryLinks(): array;

    /**
     * The reference mapping of booking roles to accounts of this chart: the
     * template the core seeds into the organization's own mapping at setup.
     * Every entry is mandatory for the organization.
     *
     * @return list<RoleMapping>
     */
    public function roles(): array;

    /**
     * The reference account blocks (bank, cash, liquid assets, fixed assets)
     * of this chart, seeded into the organization's own ranges at setup.
     *
     * @return list<AccountRange>
     */
    public function ranges(): array;
}
