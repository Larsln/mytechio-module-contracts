<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Fiscal\AutoBookingLink;
use MyTechIO\Contracts\Fiscal\ChartAccount;
use MyTechIO\Contracts\Fiscal\ChartOfAccounts;
use MyTechIO\Contracts\Fiscal\TaxCategoryLink;
use MyTechIO\Contracts\Fiscal\TaxCategoryLinkKind;

/**
 * Test double for `ChartOfAccounts` of `FakeFiscalProfile`: a three-account chart
 * (receivables, domestic revenue, export revenue).
 */
final class FakeChartOfAccounts implements ChartOfAccounts
{
    public function all(): array
    {
        return [
            new ChartAccount('1000', 'Receivables', 'asset', isCore: true),
            new ChartAccount('8000', 'Domestic revenue', 'revenue', isCore: true),
            new ChartAccount('8100', 'Export revenue', 'revenue', isCore: true),
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
        ];
    }

    public function taxCategoryLinks(): array
    {
        return [
            new TaxCategoryLink('standard', '8000', TaxCategoryLinkKind::AccountDefault),
            new TaxCategoryLink('standard', '8000', TaxCategoryLinkKind::CategoryRevenue),
        ];
    }
}
