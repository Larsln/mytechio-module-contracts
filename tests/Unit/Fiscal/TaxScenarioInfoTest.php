<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fiscal\TaxScenarioInfo;

it('uses the reason note where the profile has one and falls back to the scenario note', function () {
    $scenario = new TaxScenarioInfo('not_taxable', 'Not taxable', 'Abroad.', 'Not taxable', 'n/t', 'Generic note', ['business' => 'Business note']);

    expect($scenario->noteFor('business'))->toBe('Business note')
        ->and($scenario->noteFor('unknown'))->toBe('Generic note')
        ->and($scenario->noteFor())->toBe('Generic note');
});

it('lists every note a scenario can put on a document, without the zero-rate note', function () {
    $scenario = new TaxScenarioInfo('not_taxable', 'Not taxable', 'Abroad.', 'Not taxable', null, 'Generic', ['a' => 'A note', 'b' => 'A note'], 'Zero-rate note');

    expect($scenario->standardNotes())->toBe(['Generic', 'A note'])
        ->and((new TaxScenarioInfo('x', 'X', 'x', 'x'))->standardNotes())->toBe([]);
});

it('formats the chip label with the rate and the country', function (string $template, string $rate, string $expected) {
    expect((new TaxScenarioInfo('x', 'X', 'x', $template))->chip($rate, 'at'))->toBe($expected);
})->with([
    'whole rate' => ['VAT {rate}', '19.00', 'VAT 19 %'],
    'rate without decimals' => ['VAT {rate}', '7', 'VAT 7 %'],
    'fractional rate' => ['VAT {rate}', '5.50', 'VAT 5,5 %'],
    'zero rate' => ['VAT {rate}', '0.00', 'VAT 0 %'],
    'country and rate' => ['OSS {country} {rate}', '20.00', 'OSS AT 20 %'],
    'plain label' => ['Export', '0.00', 'Export'],
]);

it('converts a scenario to an array', function () {
    $scenario = new TaxScenarioInfo('eu_b2b_igl', 'IC supply', 'Exempt.', 'IC', 'IC', 'Exempt supply', [], null, ['w' => 'Warn'], true, false);

    expect($scenario->toArray())->toBe([
        'code' => 'eu_b2b_igl',
        'label' => 'IC supply',
        'description' => 'Exempt.',
        'chip_label' => 'IC',
        'abbreviation' => 'IC',
        'invoice_note' => 'Exempt supply',
        'reason_notes' => [],
        'zero_rate_note' => null,
        'warnings' => ['w' => 'Warn'],
        'export_proof_required' => true,
        'supported' => false,
    ]);
});
