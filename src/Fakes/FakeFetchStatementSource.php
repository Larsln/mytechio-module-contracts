<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Banking\FetchStatementSource;
use MyTechIO\Contracts\Banking\ParsedStatementSet;
use MyTechIO\Contracts\Banking\StatementContext;
use MyTechIO\Contracts\Banking\StatementParseException;
use MyTechIO\Contracts\Modules\HealthStatus;

/**
 * Test double for `FetchStatementSource`: returns a predefined
 * `ParsedStatementSet` (or throws a predefined `StatementParseException`),
 * answers `connectionStatus()` with a configurable `HealthStatus` and
 * records every call to `fetch()`.
 */
final class FakeFetchStatementSource implements FetchStatementSource
{
    /**
     * @var list<array{iban: string, from: string, to: string, context: StatementContext}>
     */
    private array $calls = [];

    public function __construct(
        private string $sourceKey = 'fake-fetch-source',
        private string $label = 'Fake-Bankabruf',
        private ?ParsedStatementSet $set = null,
        private ?StatementParseException $throws = null,
        private ?HealthStatus $status = null,
    ) {}

    public function sourceKey(): string
    {
        return $this->sourceKey;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function kind(): string
    {
        return self::KIND_FETCH;
    }

    public function fetch(string $accountIban, string $from, string $to, StatementContext $context): ParsedStatementSet
    {
        $this->calls[] = ['iban' => $accountIban, 'from' => $from, 'to' => $to, 'context' => $context];

        if ($this->throws !== null) {
            throw $this->throws;
        }

        return $this->set ?? FakeFileStatementSource::sampleSet();
    }

    public function connectionStatus(string $accountIban): HealthStatus
    {
        return $this->status ?? HealthStatus::ok('Verbindung aktiv.');
    }

    public function returns(ParsedStatementSet $set): self
    {
        $this->set = $set;

        return $this;
    }

    public function throws(StatementParseException $exception): self
    {
        $this->throws = $exception;

        return $this;
    }

    public function withStatus(HealthStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return list<array{iban: string, from: string, to: string, context: StatementContext}>
     */
    public function calls(): array
    {
        return $this->calls;
    }
}
