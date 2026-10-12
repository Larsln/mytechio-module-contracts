<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Articles\TaxClassification;
use MyTechIO\Contracts\Fiscal\CustomerRegion;
use MyTechIO\Contracts\Fiscal\CustomerTaxContext;
use MyTechIO\Contracts\Fiscal\LineTaxContext;
use MyTechIO\Contracts\Fiscal\ScenarioDecision;
use MyTechIO\Contracts\Fiscal\TaxCategoryInfo;
use MyTechIO\Contracts\Fiscal\TaxRateRow;
use MyTechIO\Contracts\Fiscal\TaxRules;

/**
 * Test double for `TaxRules` of `FakeFiscalProfile`: a customer in the profile's
 * country is `domestic`, `AT` and `FR` are EU member states, everything else is
 * a third country. Goods to a third country are exports, everything else is
 * domestic; domestic VAT is 10.00 %.
 */
final class FakeTaxRules implements TaxRules
{
    public const string DOMESTIC_RATE = '10.00';

    public function __construct(
        private readonly string $country = 'XX',
    ) {}

    public function regionOf(string $countryCode): CustomerRegion
    {
        return match (true) {
            strtoupper($countryCode) === $this->country => CustomerRegion::Domestic,
            in_array(strtoupper($countryCode), ['AT', 'FR'], true) => CustomerRegion::EuMember,
            default => CustomerRegion::ThirdCountry,
        };
    }

    public function decideScenario(CustomerTaxContext $customer, LineTaxContext $line): ScenarioDecision
    {
        $classification = $line->classification ?? TaxClassification::SupplyOfGoods;
        $export = $customer->region === CustomerRegion::ThirdCountry && $classification === TaxClassification::SupplyOfGoods;

        return new ScenarioDecision(
            automatic: $export ? 'third_country_export' : 'de_domestic',
            allowed: $customer->region === CustomerRegion::Domestic ? ['de_domestic'] : ['third_country_export', 'de_domestic'],
            explanation: $export ? 'Export.' : 'Domestic.',
            classificationMissing: $line->classification === null,
            businessProofMissingChangesResult: false,
        );
    }

    public function describeCustomer(CustomerTaxContext $customer): string
    {
        return 'Customer in '.$customer->countryCode;
    }

    public function rate(string $scenario, string $countryCode, string $taxCategoryCode, string $date): ?string
    {
        return match ($scenario) {
            'de_domestic' => $taxCategoryCode === 'zero' ? '0.00' : self::DOMESTIC_RATE,
            'third_country_export' => '0.00',
            default => null,
        };
    }

    public function ratesFor(string $countryCode, string $date): array
    {
        return strtoupper($countryCode) === $this->country ? ['standard' => self::DOMESTIC_RATE, 'zero' => '0.00'] : [];
    }

    public function revenueAccount(string $scenario, string $taxCategoryCode): string
    {
        return $scenario === 'third_country_export' ? '8100' : ($taxCategoryCode === 'zero' ? '8200' : '8000');
    }

    public function expenseAccount(string $incomingScenario, string $taxCategoryCode): ?string
    {
        return $taxCategoryCode === 'standard' ? '4000' : null;
    }

    public function restrictedExpenseAccounts(): array
    {
        return [];
    }

    public function exclusiveExpenseAccounts(): array
    {
        return [];
    }

    public function taxCategories(): array
    {
        return [
            new TaxCategoryInfo('standard', 'Standard 10 %', self::DOMESTIC_RATE),
            new TaxCategoryInfo('zero', 'Zero', '0.00'),
        ];
    }

    public function taxRateTable(): array
    {
        return [
            new TaxRateRow($this->country, 'standard', self::DOMESTIC_RATE, '2020-01-01'),
            new TaxRateRow($this->country, 'zero', '0.00', '2020-01-01'),
        ];
    }
}
