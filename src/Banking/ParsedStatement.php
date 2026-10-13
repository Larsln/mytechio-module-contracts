<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Banking;

/**
 * One statement block of a file or fetch window: period, balances and the
 * booked lines. Balances are signed decimal strings with four fractional
 * digits; a source that cannot know them (plain CSV) leaves them null.
 */
final readonly class ParsedStatement
{
    /**
     * @param  string  $currency  ISO 4217 code of the statement
     * @param  string|null  $sourceReference  statement id of the bank (camt `Stmt/Id`, MT940 `:20:`)
     * @param  string|null  $periodFrom  `YYYY-MM-DD`
     * @param  string|null  $periodTo  `YYYY-MM-DD`
     * @param  string|null  $openingBalance  signed, four fractional digits
     * @param  string|null  $closingBalance  signed, four fractional digits
     * @param  list<ParsedTransaction>  $transactions
     * @param  string|null  $accountIban  IBAN the statement belongs to (upper case, no spaces), when the format carries it; the core refuses statements of another account
     * @param  int|null  $sequenceNumber  running statement number of the bank
     * @param  array<string, mixed>  $metadata  source-specific details kept for the audit trail
     *
     * @throws StatementParseException on a malformed date or balance
     */
    public function __construct(
        public ?string $sourceReference,
        public ?string $periodFrom,
        public ?string $periodTo,
        public ?string $openingBalance,
        public ?string $closingBalance,
        public array $transactions,
        public string $currency,
        public ?string $accountIban = null,
        public ?int $sequenceNumber = null,
        public array $metadata = [],
    ) {
        StatementFormat::assertDate($periodFrom, 'periodFrom');
        StatementFormat::assertDate($periodTo, 'periodTo');
        StatementFormat::assertAmount($openingBalance, 'openingBalance');
        StatementFormat::assertAmount($closingBalance, 'closingBalance');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'source_reference' => $this->sourceReference,
            'period_from' => $this->periodFrom,
            'period_to' => $this->periodTo,
            'opening_balance' => $this->openingBalance,
            'closing_balance' => $this->closingBalance,
            'currency' => $this->currency,
            'account_iban' => $this->accountIban,
            'sequence_number' => $this->sequenceNumber,
            'metadata' => $this->metadata,
            'transactions' => array_map(
                static fn (ParsedTransaction $transaction): array => $transaction->toArray(),
                $this->transactions,
            ),
        ];
    }

    /**
     * Inverse of `toArray()`.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws StatementParseException
     */
    public static function fromArray(array $data): self
    {
        $transactions = is_array($data['transactions'] ?? null) ? $data['transactions'] : [];
        $metadata = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];
        $sequence = $data['sequence_number'] ?? null;

        return new self(
            sourceReference: StatementFormat::string($data, 'source_reference'),
            periodFrom: StatementFormat::string($data, 'period_from'),
            periodTo: StatementFormat::string($data, 'period_to'),
            openingBalance: StatementFormat::string($data, 'opening_balance'),
            closingBalance: StatementFormat::string($data, 'closing_balance'),
            transactions: array_values(array_map(
                static fn (array $transaction): ParsedTransaction => ParsedTransaction::fromArray($transaction),
                array_filter($transactions, 'is_array'),
            )),
            currency: StatementFormat::string($data, 'currency') ?? '',
            accountIban: StatementFormat::string($data, 'account_iban'),
            sequenceNumber: is_int($sequence) ? $sequence : null,
            metadata: $metadata,
        );
    }
}
