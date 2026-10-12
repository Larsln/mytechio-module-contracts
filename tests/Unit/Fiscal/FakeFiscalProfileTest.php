<?php

declare(strict_types=1);

use MyTechIO\Contracts\Articles\TaxClassification;
use MyTechIO\Contracts\Fakes\FakeFiscalProfile;
use MyTechIO\Contracts\Fakes\FakeFiscalProfiles;
use MyTechIO\Contracts\Fiscal\AccountNotMappedException;
use MyTechIO\Contracts\Fiscal\AccountRangeKind;
use MyTechIO\Contracts\Fiscal\AccountRangeNotMappedException;
use MyTechIO\Contracts\Fiscal\AccountRole;
use MyTechIO\Contracts\Fiscal\CustomerRegion;
use MyTechIO\Contracts\Fiscal\CustomerStatus;
use MyTechIO\Contracts\Fiscal\CustomerTaxContext;
use MyTechIO\Contracts\Fiscal\LineTaxContext;
use MyTechIO\Contracts\Fiscal\UnknownFiscalProfileException;
use MyTechIO\Contracts\Fiscal\UnknownScenarioException;

it('describes a small synthetic profile with two scenarios', function () {
    $profile = new FakeFiscalProfile;

    expect($profile->code())->toBe('fake')
        ->and($profile->country())->toBe('XX')
        ->and($profile->currency())->toBe('EUR')
        ->and(array_map(fn ($scenario) => $scenario->code, $profile->scenarios()))->toBe(['de_domestic', 'third_country_export'])
        ->and(array_map(fn ($scenario) => $scenario->code, $profile->incomingScenarios()))->toBe(['de_domestic', 'rc_13b'])
        ->and($profile->scenario('third_country_export')->exportProofRequired)->toBeTrue()
        ->and($profile->incomingScenario('rc_13b')->label)->toBe('Fake reverse charge')
        ->and(array_map(fn ($kind) => $kind->code, $profile->returns()->kinds()))->toBe(['vat_advance'])
        ->and(array_map(fn ($layout) => $layout->code, $profile->closing()->statements()))->toBe(['balance_sheet', 'profit_and_loss'])
        ->and(array_map(fn ($format) => $format->code, $profile->invoiceFormats()->formats()))->toBe(['pdf'])
        ->and($profile->exports()->kinds())->toBe([]);
});

it('throws for a scenario the profile does not define', function () {
    (new FakeFiscalProfile)->scenario('eu_b2c_oss');
})->throws(UnknownScenarioException::class);

it('marks the export scenario as unsupported on request', function () {
    expect(FakeFiscalProfile::withUnsupportedExport()->scenario('third_country_export')->supported)->toBeFalse()
        ->and((new FakeFiscalProfile)->scenario('third_country_export')->supported)->toBeTrue();
});

it('decides the scenario with the synthetic rule table', function () {
    $rules = (new FakeFiscalProfile)->taxRules();
    $goods = new LineTaxContext(TaxClassification::SupplyOfGoods);

    expect($rules->regionOf('xx'))->toBe(CustomerRegion::Domestic)
        ->and($rules->regionOf('AT'))->toBe(CustomerRegion::EuMember)
        ->and($rules->regionOf('US'))->toBe(CustomerRegion::ThirdCountry)
        ->and($rules->decideScenario(new CustomerTaxContext(CustomerRegion::ThirdCountry, CustomerStatus::Consumer, 'US'), $goods)->automatic)->toBe('third_country_export')
        ->and($rules->decideScenario(new CustomerTaxContext(CustomerRegion::Domestic, CustomerStatus::Business, 'XX'), $goods)->allowed)->toBe(['de_domestic'])
        ->and($rules->decideScenario(new CustomerTaxContext(CustomerRegion::EuMember, CustomerStatus::Consumer, 'AT'), new LineTaxContext(null))->classificationMissing)->toBeTrue()
        ->and($rules->rate('de_domestic', 'XX', 'standard', '2026-01-01'))->toBe('10.00')
        ->and($rules->rate('de_domestic', 'XX', 'zero', '2026-01-01'))->toBe('0.00')
        ->and($rules->rate('eu_b2c_oss', 'AT', 'standard', '2026-01-01'))->toBeNull()
        ->and($rules->ratesFor('XX', '2026-01-01'))->toBe(['standard' => '10.00', 'zero' => '0.00'])
        ->and($rules->ratesFor('AT', '2026-01-01'))->toBe([])
        ->and($rules->revenueAccount('third_country_export', 'standard'))->toBe('8100')
        ->and($rules->revenueAccount('de_domestic', 'zero'))->toBe('8200')
        ->and($rules->revenueAccount('de_domestic', 'standard'))->toBe('8000')
        ->and($rules->expenseAccount('de_domestic', 'standard'))->toBe('4000')
        ->and($rules->expenseAccount('de_domestic', 'zero'))->toBeNull()
        ->and($rules->restrictedExpenseAccounts())->toBe([])
        ->and($rules->exclusiveExpenseAccounts())->toBe([])
        ->and($rules->describeCustomer(new CustomerTaxContext(CustomerRegion::ThirdCountry, CustomerStatus::Consumer, 'US')))->toBe('Customer in US')
        ->and(array_map(fn ($category) => $category->code, $rules->taxCategories()))->toBe(['standard', 'zero'])
        ->and(count($rules->taxRateTable()))->toBe(2);
});

it('maps account roles and reports unmapped ones', function () {
    $accounts = (new FakeFiscalProfile)->accounts();

    expect($accounts->number(AccountRole::Receivable))->toBe('1000')
        ->and($accounts->number(AccountRole::OutputVatDue, '19', 'de_domestic'))->toBe('1776')
        ->and($accounts->has(AccountRole::Transit))->toBeTrue()
        ->and($accounts->vatAccounts())->toBe(['1776', '1775', '1576', '1787'])
        ->and($accounts->range(AccountRangeKind::BankLedger)->contains('1250'))->toBeTrue()
        ->and($accounts->coreAccounts())->toBe(['1000', '1600', '8000']);

    $accounts->unmap(AccountRole::Transit);
    expect($accounts->has(AccountRole::Transit))->toBeFalse();
    $accounts->number(AccountRole::Transit);
})->throws(AccountNotMappedException::class, 'Kein Konto gemappt für die Rolle „transit“.');

it('answers a seeded account for one rate and scenario and the role default otherwise', function () {
    $accounts = (new FakeFiscalProfile)->accounts()->seed(AccountRole::OutputVatDue, '3806', '19', 'de_domestic');

    expect($accounts->number(AccountRole::OutputVatDue, '19', 'de_domestic'))->toBe('3806')
        ->and($accounts->number(AccountRole::OutputVatDue, '7', 'de_domestic'))->toBe('1776')
        ->and($accounts->has(AccountRole::OutputVatDue, '19', 'de_domestic'))->toBeTrue();

    $accounts->unmap(AccountRole::OutputVatDue);

    expect($accounts->has(AccountRole::OutputVatDue, '19', 'de_domestic'))->toBeFalse();
});

it('reports the scenario and rate of an unmapped role in the message', function () {
    $accounts = (new FakeFiscalProfile)->accounts()->unmap(AccountRole::InputVat);

    $accounts->number(AccountRole::InputVat, '19', 'rc_13b');
})->throws(AccountNotMappedException::class, 'Kein Konto gemappt für die Rolle „input_vat“, Szenario „rc_13b“, Satz „19“.');

it('serves the chart with its links', function () {
    $chart = (new FakeFiscalProfile)->chart();

    expect($chart->name())->toBe('Fake chart')
        ->and(count($chart->all()))->toBe(4)
        ->and($chart->find('8000')?->name)->toBe('Domestic revenue')
        ->and($chart->find('9999'))->toBeNull()
        ->and($chart->autoBookingLinks()[1]->taxCategoryCode)->toBeNull()
        ->and($chart->taxCategoryLinks()[0]->taxCategoryCode)->toBe('standard');
});

it('binds an expense account to an incoming scenario in the fake chart', function () {
    $links = (new FakeFiscalProfile)->chart()->autoBookingLinks();

    expect($links)->toHaveCount(3)
        ->and($links[2]->scenario)->toBe('rc_13b')
        ->and($links[2]->accountNumber)->toBe('4000')
        ->and($links[2]->taxCategoryCode)->toBeNull();
});

it('serves the reference roles and ranges of the fake chart like the fake account map', function () {
    $chart = (new FakeFiscalProfile)->chart();
    $accounts = (new FakeFiscalProfile)->accounts();

    expect($chart->roles())->toHaveCount(count(AccountRole::cases()));

    foreach ($chart->roles() as $mapping) {
        expect($mapping->accountNumber)->toBe($accounts->number($mapping->role));
    }

    expect(array_map(fn ($range) => $range->toArray(), $chart->ranges()))
        ->toBe(array_map(fn ($range) => $range->toArray(), $accounts->ranges()));
});

it('resolves the current profile from the fake registry', function () {
    $registry = new FakeFiscalProfiles;

    expect($registry->current()->code())->toBe('fake')
        ->and($registry->find('fake'))->toBeInstanceOf(FakeFiscalProfile::class)
        ->and($registry->find('de'))->toBeNull()
        ->and(count($registry->all()))->toBe(1);
});

it('fixes the registry to another profile and fails for an unknown one', function () {
    $registry = new FakeFiscalProfiles([new FakeFiscalProfile('one'), new FakeFiscalProfile('two')]);

    expect($registry->current()->code())->toBe('one')
        ->and($registry->fix('two')->current()->code())->toBe('two');

    $registry->fix('missing')->current();
})->throws(UnknownFiscalProfileException::class, 'Das Steuerprofil „missing“ ist nicht verfügbar');

it('names the kind of a block of account numbers that is not mapped', function () {
    throw new AccountRangeNotMappedException(AccountRangeKind::BankLedger);
})->throws(AccountRangeNotMappedException::class, 'Kein Kontenbereich festgelegt für „bank_ledger“.');
