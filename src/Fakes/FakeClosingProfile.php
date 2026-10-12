<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Fiscal\AccountRole;
use MyTechIO\Contracts\Fiscal\AccountSpan;
use MyTechIO\Contracts\Fiscal\ClosingProfile;
use MyTechIO\Contracts\Fiscal\ClosingRule;
use MyTechIO\Contracts\Fiscal\FiscalYearRules;
use MyTechIO\Contracts\Fiscal\StatementLayout;
use MyTechIO\Contracts\Fiscal\StatementPosition;
use MyTechIO\Contracts\Fiscal\StatementSide;

/**
 * Test double for `ClosingProfile` of `FakeFiscalProfile`: a balance sheet with
 * one asset and one liability position and a profit-and-loss statement with one
 * position, the carry-forward close into retained earnings and a fiscal year
 * that may start in any month.
 */
final class FakeClosingProfile implements ClosingProfile
{
    public function statements(): array
    {
        return [
            new StatementLayout(
                StatementLayout::BALANCE_SHEET,
                'Fake balance sheet',
                [
                    new StatementSide('assets', false, [new StatementPosition('assets', 'Assets', [new AccountSpan('1000', '1999')], group: 'Assets')]),
                    new StatementSide('liabilities', true, [new StatementPosition('liabilities', 'Liabilities', [new AccountSpan('2000', '2999')], group: 'Liabilities')]),
                ],
                new StatementPosition('unassigned', 'Unassigned accounts', group: 'Unassigned'),
                new StatementPosition('result', 'Result of the period', group: 'Liabilities'),
            ),
            new StatementLayout(
                StatementLayout::PROFIT_AND_LOSS,
                'Fake profit and loss',
                [new StatementSide('positions', true, [new StatementPosition('revenue', 'Revenue', [new AccountSpan('4000', '8999')])])],
                new StatementPosition('unassigned', 'Unassigned result accounts'),
            ),
        ];
    }

    public function closingEntries(): array
    {
        return [new ClosingRule(ClosingRule::CARRY_FORWARD, 'Opening balances from fiscal year {year}', 'CLOSE-{year}', AccountRole::RetainedEarnings)];
    }

    public function fiscalYearRules(): FiscalYearRules
    {
        return new FiscalYearRules(1, range(1, 12));
    }
}
