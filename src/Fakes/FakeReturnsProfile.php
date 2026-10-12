<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Fiscal\RecapitulativeLayout;
use MyTechIO\Contracts\Fiscal\ReturnAmount;
use MyTechIO\Contracts\Fiscal\ReturnBox;
use MyTechIO\Contracts\Fiscal\ReturnBoxGrouping;
use MyTechIO\Contracts\Fiscal\ReturnInfoLine;
use MyTechIO\Contracts\Fiscal\ReturnKind;
use MyTechIO\Contracts\Fiscal\ReturnPeriod;
use MyTechIO\Contracts\Fiscal\ReturnPeriodRules;
use MyTechIO\Contracts\Fiscal\ReturnSection;
use MyTechIO\Contracts\Fiscal\ReturnSide;
use MyTechIO\Contracts\Fiscal\ReturnsProfile;
use MyTechIO\Contracts\Fiscal\ReturnTaxEffect;
use MyTechIO\Contracts\Fiscal\ReturnTerm;
use MyTechIO\Contracts\Fiscal\TaxCodeInfo;
use MyTechIO\Contracts\Fiscal\TaxCodePurpose;
use MyTechIO\Contracts\Fiscal\VatBasis;

/**
 * Test double for `ReturnsProfile` of `FakeFiscalProfile`: one VAT return of the
 * synthetic profile with a per-rate revenue box and an input-VAT box, quarterly
 * periods due on the last day of the following month, no recapitulative
 * statement. Tax codes: `fx_base_<rate>` / `fx_vat_<rate>` for revenue,
 * `fx_in_base_<rate>` / `fx_in_vat_<rate>` for purchases.
 */
final class FakeReturnsProfile implements ReturnPeriodRules, ReturnsProfile
{
    public function kinds(): array
    {
        return [new ReturnKind(ReturnKind::VAT_ADVANCE, 'Fake VAT return')];
    }

    public function taxCodes(): array
    {
        return [
            new TaxCodeInfo('fx_base_', 'Taxable revenue', false, isPrefix: true),
            new TaxCodeInfo('fx_vat_', 'Output VAT', false, isPrefix: true),
            new TaxCodeInfo('fx_in_base_', 'Purchase base', true, isPrefix: true),
            new TaxCodeInfo('fx_in_vat_', 'Input VAT', true, isPrefix: true),
        ];
    }

    public function taxCodeFor(TaxCodePurpose $purpose, string $scenario, string $rateKey, ?VatBasis $basis = null): ?string
    {
        return match ($purpose) {
            TaxCodePurpose::RevenueBase => $scenario === 'de_domestic' ? "fx_base_{$rateKey}" : null,
            TaxCodePurpose::OutputVat => $scenario === 'de_domestic' ? "fx_vat_{$rateKey}" : null,
            TaxCodePurpose::ExpenseBase => "fx_in_base_{$rateKey}",
            TaxCodePurpose::InputVat => "fx_in_vat_{$rateKey}",
            default => null,
        };
    }

    public function isDebitPositive(string $taxCode): bool
    {
        return str_starts_with($taxCode, 'fx_in_');
    }

    public function vatAdvanceLayout(): array
    {
        return [
            new ReturnSection('Revenue', ReturnTaxEffect::Payable, [
                new ReturnBox(
                    kz: 'R',
                    label: 'Revenue at {rate} %',
                    base: new ReturnAmount([new ReturnTerm('fx_base_{rate}')]),
                    tax: new ReturnAmount([new ReturnTerm('fx_vat_{rate}')]),
                    grouping: ReturnBoxGrouping::PerRate,
                    rateKeyPrefixes: ['fx_base_', 'fx_vat_'],
                    excludedRateKeys: ['0'],
                ),
            ], sortRows: true),
            new ReturnSection('Input VAT', ReturnTaxEffect::Deductible, [
                new ReturnBox(
                    kz: 'V',
                    label: 'Input VAT',
                    tax: new ReturnAmount([new ReturnTerm('fx_in_vat_', isPrefix: true)], ReturnSide::Debit),
                ),
            ]),
        ];
    }

    public function vatAdvanceInfo(): array
    {
        return [new ReturnInfoLine('exempt_base', 'Exempt revenue', new ReturnTerm('fx_base_0'), isTax: false)];
    }

    public function recapitulativeLayout(): ?RecapitulativeLayout
    {
        return null;
    }

    public function periodRules(): ReturnPeriodRules
    {
        return $this;
    }

    public function frequencies(): array
    {
        return ['quarter'];
    }

    public function periods(string $returnKind, int $calendarYear, string $frequency, bool $extended): array
    {
        if ($returnKind !== ReturnKind::VAT_ADVANCE || $frequency !== 'quarter') {
            throw new \InvalidArgumentException("The fake profile has no {$frequency} periods for {$returnKind}.");
        }

        $periods = [];
        foreach ([1, 2, 3, 4] as $no) {
            $endMonth = $no * 3;
            $start = new \DateTimeImmutable(sprintf('%d-%02d-01', $calendarYear, $endMonth - 2));
            $end = $start->modify('+2 months')->modify('last day of this month');

            $periods[] = new ReturnPeriod(
                $no,
                "Q{$no} {$calendarYear}",
                $start->format('Y-m-d'),
                $end->format('Y-m-d'),
                $end->modify('first day of next month')->modify('last day of this month')->format('Y-m-d'),
            );
        }

        return $periods;
    }
}
