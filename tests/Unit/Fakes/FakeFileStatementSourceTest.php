<?php

declare(strict_types=1);

use MyTechIO\Contracts\Banking\FileStatementSource;
use MyTechIO\Contracts\Banking\ParsedStatementSet;
use MyTechIO\Contracts\Banking\StatementContext;
use MyTechIO\Contracts\Banking\StatementParseException;
use MyTechIO\Contracts\Fakes\FakeFileStatementSource;

it('exposes defaults for key, label, kind, accepted types and confidence', function () {
    $source = new FakeFileStatementSource;

    expect($source)->toBeInstanceOf(FileStatementSource::class)
        ->and($source->sourceKey())->toBe('fake-file-source')
        ->and($source->label())->toBe('Fake-Dateiimport')
        ->and($source->kind())->toBe('file')
        ->and($source->accepts())->toBe(['csv', 'text/csv'])
        ->and($source->detect('a.csv', 'x'))->toBe(100);
});

it('returns the configured set and records every call', function () {
    $set = new ParsedStatementSet([], ['Hinweis']);
    $source = (new FakeFileStatementSource)->returns($set);
    $context = new StatementContext('DE02120300000000202051', 'EUR', ['csv_profile' => 'p']);

    $result = $source->parse('payload', $context);

    expect($result)->toBe($set)
        ->and($source->calls())->toHaveCount(1)
        ->and($source->calls()[0]['payload'])->toBe('payload')
        ->and($source->calls()[0]['context'])->toBe($context);
});

it('falls back to the sample set', function () {
    $result = (new FakeFileStatementSource)->parse('x', new StatementContext(null, 'EUR'));

    expect($result->transactionCount())->toBe(2);
});

it('throws the configured exception', function () {
    $source = (new FakeFileStatementSource)->throws(new StatementParseException('kaputt'));

    expect(fn () => $source->parse('x', new StatementContext(null, 'EUR')))
        ->toThrow(StatementParseException::class, 'kaputt');
});

it('allows changing the detection confidence', function () {
    expect((new FakeFileStatementSource)->withConfidence(0)->detect('a.xml', ''))->toBe(0);
});
