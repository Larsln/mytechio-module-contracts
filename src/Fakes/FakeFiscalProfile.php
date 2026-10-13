<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Fiscal\ChartOfAccounts;
use MyTechIO\Contracts\Fiscal\ClosingProfile;
use MyTechIO\Contracts\Fiscal\Exports;
use MyTechIO\Contracts\Fiscal\FiscalProfile;
use MyTechIO\Contracts\Fiscal\InvoiceFormats;
use MyTechIO\Contracts\Fiscal\ReturnsProfile;
use MyTechIO\Contracts\Fiscal\TaxRules;
use MyTechIO\Contracts\Fiscal\TaxScenarioInfo;
use MyTechIO\Contracts\Fiscal\UnknownScenarioException;

/**
 * Test double for `FiscalProfile`: a small synthetic profile (country `XX`,
 * 10 % domestic VAT) with two outgoing scenarios (`de_domestic`,
 * `third_country_export`) and two incoming ones (`de_domestic`, `rc_13b`),
 * `FakeTaxRules`, `FakeAccountMap` and `FakeChartOfAccounts`. The scenario
 * codes are the core's EU-wide vocabulary; the texts are the fake's own.
 * `withUnsupportedExport()` marks the export scenario as unsupported to test
 * how the core refuses it.
 */
final class FakeFiscalProfile implements FiscalProfile
{
    private readonly FakeTaxRules $taxRules;

    private readonly FakeAccountMap $accounts;

    private readonly FakeChartOfAccounts $chart;

    public function __construct(
        private readonly string $code = 'fake',
        private readonly bool $exportSupported = true,
    ) {
        $this->taxRules = new FakeTaxRules($this->country());
        $this->accounts = new FakeAccountMap;
        $this->chart = new FakeChartOfAccounts;
    }

    public static function withUnsupportedExport(string $code = 'fake'): self
    {
        return new self($code, exportSupported: false);
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return 'Fake profile';
    }

    public function country(): string
    {
        return 'XX';
    }

    public function currency(): string
    {
        return 'EUR';
    }

    public function scenarios(): array
    {
        return [
            new TaxScenarioInfo('de_domestic', 'Fake domestic', 'Domestic VAT at the category rate.', 'Domestic {rate}', zeroRateNote: 'Exempt turnover'),
            new TaxScenarioInfo('third_country_export', 'Fake export', 'Exempt export.', 'Export', 'EXP', 'Exempt export supply', exportProofRequired: true, supported: $this->exportSupported),
        ];
    }

    public function scenario(string $code): TaxScenarioInfo
    {
        foreach ($this->scenarios() as $scenario) {
            if ($scenario->code === $code) {
                return $scenario;
            }
        }

        throw new UnknownScenarioException($code);
    }

    public function incomingScenarios(): array
    {
        return [
            new TaxScenarioInfo('de_domestic', 'Fake domestic purchase', 'Input VAT deducted.', 'Domestic'),
            new TaxScenarioInfo('rc_13b', 'Fake reverse charge', 'Self-assessed VAT.', 'Reverse charge'),
        ];
    }

    public function incomingScenario(string $code): TaxScenarioInfo
    {
        foreach ($this->incomingScenarios() as $scenario) {
            if ($scenario->code === $code) {
                return $scenario;
            }
        }

        throw new UnknownScenarioException($code);
    }

    public function taxRules(): TaxRules
    {
        return $this->taxRules;
    }

    /** The fake's account map, to seed the mapping a test binds as `AccountMap`. */
    public function accounts(): FakeAccountMap
    {
        return $this->accounts;
    }

    public function chart(): ChartOfAccounts
    {
        return $this->chart;
    }

    public function returns(): ReturnsProfile
    {
        return new FakeReturnsProfile;
    }

    public function closing(): ClosingProfile
    {
        return new FakeClosingProfile;
    }

    public function invoiceFormats(): InvoiceFormats
    {
        return new FakeInvoiceFormats;
    }

    public function exports(): Exports
    {
        return new FakeExports;
    }
}
