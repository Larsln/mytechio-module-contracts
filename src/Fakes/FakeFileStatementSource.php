<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Banking\FileStatementSource;
use MyTechIO\Contracts\Banking\ParsedStatement;
use MyTechIO\Contracts\Banking\ParsedStatementSet;
use MyTechIO\Contracts\Banking\ParsedTransaction;
use MyTechIO\Contracts\Banking\StatementContext;
use MyTechIO\Contracts\Banking\StatementParseException;

/**
 * Test double for `FileStatementSource`: accepts the configured extensions,
 * reports a fixed detection confidence, returns a predefined
 * `ParsedStatementSet` (or throws a predefined `StatementParseException`)
 * and records every call to `parse()`.
 */
final class FakeFileStatementSource implements FileStatementSource
{
    /**
     * @var list<array{payload: string, context: StatementContext}>
     */
    private array $calls = [];

    /**
     * @param  list<string>  $accepts
     */
    public function __construct(
        private string $sourceKey = 'fake-file-source',
        private string $label = 'Fake-Dateiimport',
        private array $accepts = ['csv', 'text/csv'],
        private int $confidence = 100,
        private ?ParsedStatementSet $set = null,
        private ?StatementParseException $throws = null,
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
        return self::KIND_FILE;
    }

    public function accepts(): array
    {
        return $this->accepts;
    }

    public function detect(string $filename, string $payload): int
    {
        return $this->confidence;
    }

    public function parse(string $payload, StatementContext $context): ParsedStatementSet
    {
        $this->calls[] = ['payload' => $payload, 'context' => $context];

        if ($this->throws !== null) {
            throw $this->throws;
        }

        return $this->set ?? self::sampleSet();
    }

    /**
     * One statement with two booked lines (a credit and a debit) and
     * matching balances.
     */
    public static function sampleSet(): ParsedStatementSet
    {
        return new ParsedStatementSet([
            new ParsedStatement(
                sourceReference: 'FAKE-1',
                periodFrom: '2026-10-01',
                periodTo: '2026-10-02',
                openingBalance: '1000.0000',
                closingBalance: '1050.0000',
                transactions: [
                    new ParsedTransaction(
                        bookingDate: '2026-10-01',
                        amount: '100.0000',
                        currency: 'EUR',
                        counterpartyName: 'ACME GmbH',
                        purpose: 'Rechnung RE-1',
                        bankReference: 'REF-1',
                    ),
                    new ParsedTransaction(
                        bookingDate: '2026-10-02',
                        amount: '-50.0000',
                        currency: 'EUR',
                        counterpartyName: 'Muster AG',
                        purpose: 'Miete',
                        bankReference: 'REF-2',
                    ),
                ],
                currency: 'EUR',
            ),
        ]);
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

    public function withConfidence(int $confidence): self
    {
        $this->confidence = $confidence;

        return $this;
    }

    /**
     * @return list<array{payload: string, context: StatementContext}>
     */
    public function calls(): array
    {
        return $this->calls;
    }
}
