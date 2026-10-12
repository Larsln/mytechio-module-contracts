<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\TaxClassification;
use MyTechIO\Contracts\Fiscal\AccountRange;
use MyTechIO\Contracts\Fiscal\AccountRangeKind;
use MyTechIO\Contracts\Fiscal\AutoBookingLink;
use MyTechIO\Contracts\Fiscal\ChartAccount;
use MyTechIO\Contracts\Fiscal\CustomerRegion;
use MyTechIO\Contracts\Fiscal\CustomerStatus;
use MyTechIO\Contracts\Fiscal\CustomerTaxContext;
use MyTechIO\Contracts\Fiscal\LineTaxContext;
use MyTechIO\Contracts\Fiscal\ScenarioDecision;
use MyTechIO\Contracts\Fiscal\ServiceType;
use MyTechIO\Contracts\Fiscal\TaxCategoryInfo;
use MyTechIO\Contracts\Fiscal\TaxCategoryLink;
use MyTechIO\Contracts\Fiscal\TaxCategoryLinkKind;
use MyTechIO\Contracts\Fiscal\TaxRateRow;

it('checks whether an account number lies inside a range', function (string $number, bool $inside) {
    expect((new AccountRange(AccountRangeKind::BankLedger, '1800', '1889'))->contains($number))->toBe($inside);
})->with([
    'first' => ['1800', true],
    'inside' => ['1850', true],
    'last' => ['1889', true],
    'above' => ['1890', false],
    'below' => ['1799', false],
    'different length' => ['18000', false],
]);

it('knows which customer status counts as a business', function () {
    expect(CustomerStatus::Business->isBusiness())->toBeTrue()
        ->and(CustomerStatus::Consumer->isBusiness())->toBeFalse()
        ->and(CustomerStatus::BusinessWithoutProof->isBusiness())->toBeFalse();
});

it('converts the rule table contexts and the decision to arrays', function () {
    expect((new CustomerTaxContext(CustomerRegion::EuMember, CustomerStatus::Consumer, 'AT', true, false))->toArray())->toBe([
        'region' => 'eu_member',
        'status' => 'consumer',
        'country_code' => 'AT',
        'is_business_contact' => true,
        'is_small_business' => false,
    ])
        ->and((new LineTaxContext(TaxClassification::OtherService, ServiceType::OnSite))->toArray())->toBe(['classification' => 'other_service', 'service_type' => 'on_site'])
        ->and((new LineTaxContext(null))->toArray())->toBe(['classification' => null, 'service_type' => null])
        ->and((new ScenarioDecision('a', ['a', 'b'], 'Because.', false, true))->toArray())->toBe([
            'automatic' => 'a',
            'allowed' => ['a', 'b'],
            'explanation' => 'Because.',
            'classification_missing' => false,
            'business_proof_missing_changes_result' => true,
        ]);
});

it('converts the seed data DTOs to arrays', function () {
    expect((new TaxCategoryInfo('standard', 'Standard', '19.00'))->toArray())->toBe(['code' => 'standard', 'name' => 'Standard', 'default_rate' => '19.00', 'is_active' => true])
        ->and((new TaxRateRow('AT', 'standard', '20.00', '2020-01-01', 'Note'))->toArray())->toBe(['country_code' => 'AT', 'tax_category_code' => 'standard', 'rate' => '20.00', 'valid_from' => '2020-01-01', 'notes' => 'Note'])
        ->and((new ChartAccount('4400', 'Revenue', 'revenue', null, 'Revenue', true, false))->toArray())->toBe(['number' => '4400', 'name' => 'Revenue', 'type' => 'revenue', 'name_long' => null, 'category' => 'Revenue', 'is_core' => true, 'is_commonly_used' => false])
        ->and((new AutoBookingLink('4125', 'eu_b2b_igl'))->toArray())->toBe(['account_number' => '4125', 'scenario' => 'eu_b2b_igl', 'tax_category_code' => null])
        ->and((new TaxCategoryLink('standard', '4400', TaxCategoryLinkKind::CategoryRevenue))->toArray())->toBe(['tax_category_code' => 'standard', 'account_number' => '4400', 'kind' => 'category_revenue'])
        ->and((new AccountRange(AccountRangeKind::CashLedger, '1600', '1699'))->toArray())->toBe(['kind' => 'cash_ledger', 'from' => '1600', 'to' => '1699']);
});
