<?php

declare(strict_types=1);

use MyTechIO\Contracts\Banking\ParsedStatement;
use MyTechIO\Contracts\Banking\ParsedStatementSet;
use MyTechIO\Contracts\Banking\ParsedTransaction;
use MyTechIO\Contracts\Banking\StatementParseException;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeFileStatementSource;

it('round-trips a full set through toArray and fromArray', function () {
    $child = new ParsedTransaction(bookingDate: '2026-10-01', amount: '-10.0000', currency: 'EUR', purpose: 'Einzelposten');
    $parent = new ParsedTransaction(
        bookingDate: '2026-10-01',
        amount: '-30.0000',
        currency: 'EUR',
        valueDate: '2026-10-02',
        counterpartyName: 'Sammler',
        counterpartyIban: 'DE02120300000000202051',
        counterpartyBic: 'BYLADEM1001',
        purpose: 'Sammelbuchung',
        endToEndId: 'E2E-1',
        mandateReference: 'MREF-1',
        creditorId: 'DE98ZZZ09999999999',
        creditorReference: 'RF18539007547034',
        bankReference: 'BREF-1',
        transactionCode: '105',
        balanceAfter: '970.0000',
        raw: ['row' => 7],
        batchChildren: [$child],
    );
    $set = new ParsedStatementSet([
        new ParsedStatement(
            sourceReference: 'S-1',
            periodFrom: '2026-10-01',
            periodTo: '2026-10-31',
            openingBalance: '1000.0000',
            closingBalance: '970.0000',
            transactions: [$parent],
            currency: 'EUR',
            accountIban: 'DE02120300000000202051',
            sequenceNumber: 17,
            metadata: ['skipped_non_booked' => 1],
        ),
    ], ['Eine Zeile wurde übersprungen.']);

    $copy = ParsedStatementSet::fromArray($set->toArray());

    expect($copy)->toEqual($set)
        ->and($copy->statements[0]->transactions[0]->batchChildren[0]->purpose)->toBe('Einzelposten')
        ->and($copy->transactionCount())->toBe(1);
});

it('defaults optional fields to null and empty lists', function () {
    $transaction = new ParsedTransaction(bookingDate: '2026-10-01', amount: '1.0000', currency: 'EUR');

    expect($transaction->valueDate)->toBeNull()
        ->and($transaction->creditorId)->toBeNull()
        ->and($transaction->raw)->toBe([])
        ->and($transaction->batchChildren)->toBe([]);
});

it('rejects malformed dates and amounts with a StatementParseException', function (callable $make) {
    expect($make)->toThrow(StatementParseException::class);
})->with([
    'booking date' => [fn () => new ParsedTransaction(bookingDate: '01.10.2026', amount: '1.0000', currency: 'EUR')],
    'impossible date' => [fn () => new ParsedTransaction(bookingDate: '2026-02-30', amount: '1.0000', currency: 'EUR')],
    'amount scale' => [fn () => new ParsedTransaction(bookingDate: '2026-10-01', amount: '1.00', currency: 'EUR')],
    'amount comma' => [fn () => new ParsedTransaction(bookingDate: '2026-10-01', amount: '1,0000', currency: 'EUR')],
    'balance after' => [fn () => new ParsedTransaction(bookingDate: '2026-10-01', amount: '1.0000', currency: 'EUR', balanceAfter: 'x')],
    'period' => [fn () => new ParsedStatement(null, '2026-13-01', null, null, null, [], 'EUR')],
    'opening balance' => [fn () => new ParsedStatement(null, null, null, '10', null, [], 'EUR')],
]);

it('accepts negative amounts and null balances', function () {
    $statement = new ParsedStatement(null, null, null, null, '-5.0000', [], 'EUR');

    expect($statement->closingBalance)->toBe('-5.0000')
        ->and($statement->openingBalance)->toBeNull();
});

it('uses a ContractException subclass for parse failures', function () {
    expect(new StatementParseException('kaputt'))->toBeInstanceOf(ContractException::class);
});

it('serves a consistent sample set from the fake file source', function () {
    $set = FakeFileStatementSource::sampleSet();

    expect($set->transactionCount())->toBe(2)
        ->and($set->statements[0]->openingBalance)->toBe('1000.0000');
});
