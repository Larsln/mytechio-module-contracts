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
