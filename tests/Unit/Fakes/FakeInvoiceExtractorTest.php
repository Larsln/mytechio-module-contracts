<?php

declare(strict_types=1);

use MyTechIO\Contracts\Documents\ExtractionContextData;
use MyTechIO\Contracts\Documents\ExtractionFailedException;
use MyTechIO\Contracts\Fakes\FakeInvoiceExtractor;

it('exposes defaults for key, label and configured state', function () {
    $extractor = new FakeInvoiceExtractor;

    expect($extractor->extractorKey())->toBe('fake-extraction')
        ->and($extractor->label())->toBe('Fake-Extraktion')
        ->and($extractor->configured())->toBeTrue();
});

it('returns the configured payload and records every call', function () {
    $extractor = (new FakeInvoiceExtractor)->returns(['supplier' => ['name' => 'ACME']]);
    $context = new ExtractionContextData(expenseAccounts: [], units: ['Stk']);

    $payload = $extractor->extract('%PDF-1.4', $context);

    expect($payload)->toBe(['supplier' => ['name' => 'ACME']])
        ->and($extractor->calls())->toHaveCount(1)
        ->and($extractor->calls()[0]['blob'])->toBe('%PDF-1.4')
        ->and($extractor->calls()[0]['context'])->toBe($context);
});

it('throws the configured exception instead of returning a payload', function () {
    $extractor = (new FakeInvoiceExtractor)->throws(new ExtractionFailedException('kaputt'));

    $extract = fn () => $extractor->extract('blob', new ExtractionContextData(expenseAccounts: [], units: []));

    expect($extract)->toThrow(ExtractionFailedException::class, 'kaputt');
});

it('allows toggling the configured state', function () {
    $extractor = (new FakeInvoiceExtractor)->withConfigured(false);

    expect($extractor->configured())->toBeFalse();
});

it('provides a sample payload with normalised source boxes on two fields', function () {
    $payload = FakeInvoiceExtractor::samplePayload();
    $boxes = [$payload['supplier']['name']['source_box'], $payload['invoice']['number']['source_box']];

    foreach ($boxes as $box) {
        expect(array_keys($box))->toBe(['x0', 'y0', 'x1', 'y1'])
            ->and($box['x0'])->toBeGreaterThanOrEqual(0.0)
            ->and($box['x1'])->toBeLessThanOrEqual(1.0)
            ->and($box['x0'])->toBeLessThan($box['x1'])
            ->and($box['y0'])->toBeLessThan($box['y1']);
    }

    expect($payload['invoice']['date'])->not->toHaveKey('source_box');

    $extractor = (new FakeInvoiceExtractor)->returns($payload);

    expect($extractor->extract('blob', new ExtractionContextData(expenseAccounts: [], units: [])))->toBe($payload);
});
