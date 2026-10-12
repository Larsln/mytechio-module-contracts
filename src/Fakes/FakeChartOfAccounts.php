<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Fiscal\AccountRange;
use MyTechIO\Contracts\Fiscal\AccountRangeKind;
use MyTechIO\Contracts\Fiscal\AccountRole;
use MyTechIO\Contracts\Fiscal\AutoBookingLink;
use MyTechIO\Contracts\Fiscal\ChartAccount;
use MyTechIO\Contracts\Fiscal\ChartOfAccounts;
use MyTechIO\Contracts\Fiscal\RoleMapping;
use MyTechIO\Contracts\Fiscal\TaxCategoryLink;
use MyTechIO\Contracts\Fiscal\TaxCategoryLinkKind;

/**
 * Test double for `ChartOfAccounts` of `FakeFiscalProfile`: a four-account chart
 * (receivables, domestic revenue, export revenue, a reverse-charge expense
 * account bound to the incoming scenario `rc_13b`) with the reference mapping
 * and ranges of `FakeAccountMap`.
 */
final class FakeChartOfAccounts implements ChartOfAccounts
{
    public function all(): array
    {
        return [
            new ChartAccount('1000', 'Receivables', 'asset', isCore: true),
            new ChartAccount('8000', 'Domestic revenue', 'revenue', isCore: true),
            new ChartAccount('8100', 'Export revenue', 'revenue', isCore: true),
            new ChartAccount('4000', 'Reverse-charge expense', 'expense', isCore: true),
        ];
    }

    public function find(string $number): ?ChartAccount
    {
        foreach ($this->all() as $account) {
            if ($account->number === $number) {
                return $account;
            }
        }

        return null;
    }

    public function autoBookingLinks(): array
    {
        return [
            new AutoBookingLink('8000', 'de_domestic', 'standard'),
            new AutoBookingLink('8100', 'third_country_export'),
            new AutoBookingLink('4000', 'rc_13b'),
        ];
    }

    public function taxCategoryLinks(): array
    {
        return [
            new TaxCategoryLink('standard', '8000', TaxCategoryLinkKind::AccountDefault),
            new TaxCategoryLink('standard', '8000', TaxCategoryLinkKind::CategoryRevenue),
        ];
    }

    public function roles(): array
    {
        return [
            new RoleMapping(AccountRole::Receivable, null, null, '1000'),
            new RoleMapping(AccountRole::Payable, null, null, '1600'),
            new RoleMapping(AccountRole::Discount, null, null, '8700'),
            new RoleMapping(AccountRole::Transit, null, null, '1360'),
            new RoleMapping(AccountRole::RetainedEarnings, null, null, '0860'),
            new RoleMapping(AccountRole::AcquisitionCost, null, null, '3800'),
            new RoleMapping(AccountRole::GoodsExpense, null, null, '3000'),
            new RoleMapping(AccountRole::OutputVatDue, null, null, '1776'),
            new RoleMapping(AccountRole::OutputVatNotYetDue, null, null, '1775'),
            new RoleMapping(AccountRole::InputVat, null, null, '1576'),
            new RoleMapping(AccountRole::ReverseChargeVat, null, null, '1787'),
        ];
    }

    public function ranges(): array
    {
        return [
            new AccountRange(AccountRangeKind::BankLedger, '1200', '1299'),
            new AccountRange(AccountRangeKind::CashLedger, '1100', '1199'),
            new AccountRange(AccountRangeKind::LiquidAssets, '1100', '1299'),
            new AccountRange(AccountRangeKind::FixedAssets, '0100', '0999'),
        ];
    }
}
