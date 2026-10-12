<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeFiscalProfile;
use MyTechIO\Contracts\Fiscal\AccountRole;
use MyTechIO\Contracts\Fiscal\AccountSpan;
use MyTechIO\Contracts\Fiscal\ClosingRule;
use MyTechIO\Contracts\Fiscal\FiscalYearRules;
use MyTechIO\Contracts\Fiscal\RecapitulativeKind;
use MyTechIO\Contracts\Fiscal\RecapitulativeLayout;
use MyTechIO\Contracts\Fiscal\ReturnKind;
use MyTechIO\Contracts\Fiscal\StatementLayout;
use MyTechIO\Contracts\Fiscal\StatementPosition;
use MyTechIO\Contracts\Fiscal\TaxCodeInfo;
use MyTechIO\Contracts\Fiscal\TaxCodePurpose;
use MyTechIO\Contracts\Fiscal\VatBasis;

it('matches tax codes exactly or by prefix', function () {
    $exact = new TaxCodeInfo('base_igl', 'Intra-Community supplies', false);
    $prefix = new TaxCodeInfo('base_de_', 'Domestic base', false, isPrefix: true);

    expect($exact->matches('base_igl'))->toBeTrue()
        ->and($exact->matches('base_igl_x'))->toBeFalse()
        ->and($prefix->matches('base_de_19'))->toBeTrue()
        ->and($prefix->matches('vat_de_19'))->toBeFalse()
        ->and($prefix->toArray())->toBe(['code' => 'base_de_', 'meaning' => 'Domestic base', 'debit_positive' => false, 'is_prefix' => true]);
});

it('chooses the fake tax codes by purpose, scenario and rate', function () {
    $returns = (new FakeFiscalProfile)->returns();

    expect($returns->taxCodeFor(TaxCodePurpose::RevenueBase, 'de_domestic', '10'))->toBe('fx_base_10')
        ->and($returns->taxCodeFor(TaxCodePurpose::OutputVat, 'de_domestic', '10', VatBasis::Cash))->toBe('fx_vat_10')
        ->and($returns->taxCodeFor(TaxCodePurpose::OutputVat, 'third_country_export', '0'))->toBeNull()
        ->and($returns->taxCodeFor(TaxCodePurpose::InputVat, 'rc_13b', '10'))->toBe('fx_in_vat_10')
        ->and($returns->taxCodeFor(TaxCodePurpose::WithdrawalVat, 'de_domestic', '10'))->toBeNull()
        ->and($returns->isDebitPositive('fx_in_vat_10'))->toBeTrue()
        ->and($returns->isDebitPositive('fx_vat_10'))->toBeFalse()
        ->and(VatBasis::from('ist'))->toBe(VatBasis::Cash);
});

it('lays out the fake VAT return as serializable data', function () {
    $returns = (new FakeFiscalProfile)->returns();
    $sections = $returns->vatAdvanceLayout();

    expect($sections)->toHaveCount(2)
        ->and($sections[0]->boxes[0]->grouping->value)->toBe('per_rate')
        ->and($sections[0]->toArray()['boxes'][0]['base']['terms'][0]['code'])->toBe('fx_base_{rate}')
        ->and($sections[1]->taxEffect->value)->toBe('deductible')
        ->and($returns->vatAdvanceInfo()[0]->toArray())->toBe([
            'key' => 'exempt_base', 'label' => 'Exempt revenue', 'term' => ['code' => 'fx_base_0', 'is_prefix' => false, 'divide_by_rate' => false], 'is_tax' => false,
        ])
        ->and($returns->recapitulativeLayout())->toBeNull();
});

it('computes the fake reporting calendar and refuses unknown kinds', function () {
    $rules = (new FakeFiscalProfile)->returns()->periodRules();
    $periods = $rules->periods(ReturnKind::VAT_ADVANCE, 2026, 'quarter', false);

    expect($rules->frequencies())->toBe(['quarter'])
        ->and($periods)->toHaveCount(4)
        ->and($periods[0]->toArray())->toBe([
            'no' => 1, 'label' => 'Q1 2026', 'starts_on' => '2026-01-01', 'ends_on' => '2026-03-31', 'due_on' => '2026-04-30', 'applies_special_prepayment' => false,
        ])
        ->and($periods[3]->dueOn)->toBe('2027-01-31');

    $rules->periods(ReturnKind::RECAPITULATIVE, 2026, 'quarter', false);
})->throws(InvalidArgumentException::class);

it('maps the scenarios of a recapitulative layout to their kinds', function () {
    $layout = new RecapitulativeLayout([
        new RecapitulativeKind(RecapitulativeKind::GOODS, 'Goods', '', ['eu_b2b_igl']),
        new RecapitulativeKind(RecapitulativeKind::SERVICE, 'Service', '1', ['eu_b2b_service']),
    ], ['AT', 'FR']);

    expect($layout->kindOfScenario('eu_b2b_service')?->indicator)->toBe('1')
        ->and($layout->kindOfScenario('de_domestic'))->toBeNull()
        ->and($layout->kind('goods')?->label)->toBe('Goods')
        ->and($layout->scenarios())->toBe(['eu_b2b_igl', 'eu_b2b_service'])
        ->and($layout->toArray()['country_prefixes'])->toBe(['AT', 'FR']);
});

it('matches accounts to statement positions by span and by number', function () {
    $position = new StatementPosition('cash', 'Cash', [new AccountSpan('1600', '1899')], ['1460'], 'Current assets');

    expect($position->contains('1800'))->toBeTrue()
        ->and($position->contains('1460'))->toBeTrue()
        ->and($position->contains('1461'))->toBeFalse()
        ->and($position->contains('1900'))->toBeFalse()
        ->and((new AccountSpan('0100', '0199'))->contains('0150'))->toBeTrue();
});

it('lays out the fake closing statements and renders the closing texts', function () {
    $closing = (new FakeFiscalProfile)->closing();
    $balanceSheet = $closing->statements()[0];
    $rule = $closing->closingEntries()[0];

    expect($balanceSheet->side('liabilities')?->creditPositive)->toBeTrue()
        ->and($balanceSheet->side('missing'))->toBeNull()
        ->and($balanceSheet->resultPosition?->group)->toBe('Liabilities')
        ->and($closing->statements()[1]->code)->toBe(StatementLayout::PROFIT_AND_LOSS)
        ->and($rule->code)->toBe(ClosingRule::CARRY_FORWARD)
        ->and($rule->resultRole)->toBe(AccountRole::RetainedEarnings)
        ->and($rule->descriptionFor(2026))->toBe('Opening balances from fiscal year 2026')
        ->and($rule->documentReferenceFor(2026))->toBe('CLOSE-2026')
        ->and($closing->fiscalYearRules()->allows(7))->toBeTrue()
        ->and((new FiscalYearRules(1, [1, 4]))->allows(2))->toBeFalse();
});

it('maps the fake scenarios to invoice format categories and exemption reasons', function () {
    $formats = (new FakeFiscalProfile)->invoiceFormats();

    expect($formats->formats()[0]->toArray())->toBe(['code' => 'pdf', 'name' => 'Fake PDF', 'can_write' => true, 'can_read' => false])
        ->and($formats->lineTaxCategory('de_domestic', '10.00', 'standard'))->toBe('S')
        ->and($formats->breakdownTaxCategory('de_domestic', '0.00', 'zero'))->toBe('Z')
        ->and($formats->lineTaxCategory('third_country_export', '0.00', null))->toBe('E')
        ->and($formats->exemptionReason('E')?->toArray())->toBe(['text' => 'Exempt export', 'code' => 'FAKE-EXEMPT'])
        ->and($formats->exemptionReason('S'))->toBeNull()
        ->and($formats->mandatoryFields('pdf'))->toBe([])
        ->and($formats->rounding()->toArray())->toBe(['calculation_scale' => 4, 'display_scale' => 2, 'mode' => 'half_even']);
});

it('offers no export kinds in the fake profile', function () {
    expect((new FakeFiscalProfile)->exports()->kinds())->toBe([]);
});
