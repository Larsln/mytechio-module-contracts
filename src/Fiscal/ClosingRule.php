<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A booking the year-end close makes. `CARRY_FORWARD` carries the balance-sheet
 * balances into the following year in one entry on its first day and folds the
 * year result into the account of `resultRole`. `{year}` in the texts stands
 * for the closed fiscal year.
 */
final readonly class ClosingRule
{
    public const string CARRY_FORWARD = 'carry_forward';

    public function __construct(
        public string $code,
        public string $description,
        public string $documentReference,
        public AccountRole $resultRole,
    ) {}

    public function descriptionFor(int $fiscalYear): string
    {
        return str_replace('{year}', (string) $fiscalYear, $this->description);
    }

    public function documentReferenceFor(int $fiscalYear): string
    {
        return str_replace('{year}', (string) $fiscalYear, $this->documentReference);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'description' => $this->description,
            'document_reference' => $this->documentReference,
            'result_role' => $this->resultRole->value,
        ];
    }
}
