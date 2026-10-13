<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Banking;

/**
 * One booked line of a statement, normalised by the source. Amounts are
 * signed decimal strings with four fractional digits (credit positive, debit
 * negative), dates are `YYYY-MM-DD`.
 */
final readonly class ParsedTransaction
{
    /**
     * @param  string  $bookingDate  `YYYY-MM-DD`
     * @param  string  $amount  signed, four fractional digits, never zero-checked by the contract
     * @param  string|null  $valueDate  `YYYY-MM-DD`
     * @param  string|null  $counterpartyIban  upper case, no spaces
     * @param  string|null  $purpose  whitespace-collapsed
     * @param  string|null  $endToEndId  null instead of `NOTPROVIDED`
     * @param  string|null  $creditorId  SEPA creditor identifier (`CRED+`, `CdtrId`)
     * @param  string|null  $creditorReference  ISO 11649 reference (`RF...`)
     * @param  string|null  $bankReference  bank-assigned unique reference; null instead of `NONREF`
     * @param  string|null  $transactionCode  bank transaction code (GVC, BkTxCd)
     * @param  string|null  $balanceAfter  running balance after the line, four fractional digits
     * @param  array<string, mixed>  $raw  source-specific details kept for the audit trail
     * @param  list<ParsedTransaction>  $batchChildren  single items of a batch booking (camt.054 details)
     *
     * @throws StatementParseException on a malformed date or amount
     */
    public function __construct(
        public string $bookingDate,
        public string $amount,
        public string $currency,
        public ?string $valueDate = null,
        public ?string $counterpartyName = null,
        public ?string $counterpartyIban = null,
        public ?string $counterpartyBic = null,
        public ?string $purpose = null,
        public ?string $endToEndId = null,
        public ?string $mandateReference = null,
        public ?string $creditorId = null,
        public ?string $creditorReference = null,
        public ?string $bankReference = null,
        public ?string $transactionCode = null,
        public ?string $balanceAfter = null,
        public array $raw = [],
        public array $batchChildren = [],
    ) {
        StatementFormat::assertDate($bookingDate, 'bookingDate');
        StatementFormat::assertDate($valueDate, 'valueDate');
        StatementFormat::assertAmount($amount, 'amount');
        StatementFormat::assertAmount($balanceAfter, 'balanceAfter');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'booking_date' => $this->bookingDate,
            'value_date' => $this->valueDate,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'counterparty_name' => $this->counterpartyName,
            'counterparty_iban' => $this->counterpartyIban,
            'counterparty_bic' => $this->counterpartyBic,
            'purpose' => $this->purpose,
            'end_to_end_id' => $this->endToEndId,
            'mandate_reference' => $this->mandateReference,
            'creditor_id' => $this->creditorId,
            'creditor_reference' => $this->creditorReference,
            'bank_reference' => $this->bankReference,
            'transaction_code' => $this->transactionCode,
            'balance_after' => $this->balanceAfter,
            'raw' => $this->raw,
            'batch_children' => array_map(
                static fn (ParsedTransaction $child): array => $child->toArray(),
                $this->batchChildren,
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
        $children = is_array($data['batch_children'] ?? null) ? $data['batch_children'] : [];
        $raw = is_array($data['raw'] ?? null) ? $data['raw'] : [];

        return new self(
            bookingDate: StatementFormat::string($data, 'booking_date') ?? '',
            amount: StatementFormat::string($data, 'amount') ?? '',
            currency: StatementFormat::string($data, 'currency') ?? '',
            valueDate: StatementFormat::string($data, 'value_date'),
            counterpartyName: StatementFormat::string($data, 'counterparty_name'),
            counterpartyIban: StatementFormat::string($data, 'counterparty_iban'),
            counterpartyBic: StatementFormat::string($data, 'counterparty_bic'),
            purpose: StatementFormat::string($data, 'purpose'),
            endToEndId: StatementFormat::string($data, 'end_to_end_id'),
            mandateReference: StatementFormat::string($data, 'mandate_reference'),
            creditorId: StatementFormat::string($data, 'creditor_id'),
            creditorReference: StatementFormat::string($data, 'creditor_reference'),
            bankReference: StatementFormat::string($data, 'bank_reference'),
            transactionCode: StatementFormat::string($data, 'transaction_code'),
            balanceAfter: StatementFormat::string($data, 'balance_after'),
            raw: $raw,
            batchChildren: array_values(array_map(
                static fn (array $child): self => self::fromArray($child),
                array_filter($children, 'is_array'),
            )),
        );
    }
}
