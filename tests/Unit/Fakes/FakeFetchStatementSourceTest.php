<?php

declare(strict_types=1);

use MyTechIO\Contracts\Banking\FetchStatementSource;
use MyTechIO\Contracts\Banking\ParsedStatementSet;
use MyTechIO\Contracts\Banking\StatementContext;
use MyTechIO\Contracts\Banking\StatementParseException;
use MyTechIO\Contracts\Fakes\FakeFetchStatementSource;
use MyTechIO\Contracts\Modules\HealthStatus;

it('exposes defaults for key, label, kind and a healthy connection', function () {
    $source = new FakeFetchStatementSource;

    expect($source)->toBeInstanceOf(FetchStatementSource::class)
        ->and($source->sourceKey())->toBe('fake-fetch-source')
        ->and($source->kind())->toBe('fetch')
        ->and($source->connectionStatus('DE02120300000000202051')->state)->toBe(HealthStatus::OK);
});

it('returns the configured set and records the fetch window', function () {
    $set = new ParsedStatementSet([]);
    $source = (new FakeFetchStatementSource)->returns($set);
    $context = new StatementContext('DE02120300000000202051', 'EUR');

    $result = $source->fetch('DE02120300000000202051', '2026-10-01', '2026-10-31', $context);

    expect($result)->toBe($set)
        ->and($source->calls())->toHaveCount(1)
        ->and($source->calls()[0]['from'])->toBe('2026-10-01')
        ->and($source->calls()[0]['to'])->toBe('2026-10-31');
});

it('throws the configured exception', function () {
    $source = (new FakeFetchStatementSource)->throws(new StatementParseException('Einwilligung abgelaufen'));

    expect(fn () => $source->fetch('DE02120300000000202051', '2026-10-01', '2026-10-31', new StatementContext(null, 'EUR')))
        ->toThrow(StatementParseException::class, 'Einwilligung abgelaufen');
});

it('allows setting the connection status', function () {
    $source = (new FakeFetchStatementSource)->withStatus(HealthStatus::warning('Einwilligung läuft ab.'));

    expect($source->connectionStatus('DE02120300000000202051')->state)->toBe(HealthStatus::WARNING);
});
