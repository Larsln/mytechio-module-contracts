<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The chart of accounts a fiscal profile brings: the seed data of the
 * `accounts` table and its links to tax scenarios and tax categories. The
 * `accounts` table itself is profile-independent and user-maintainable.
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
     * The automatic accounts of the outgoing scenarios.
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
}
