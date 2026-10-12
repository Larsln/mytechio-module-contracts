<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Read-only reference to one line item of an incoming invoice.
 *
 * `quantity` is `null` for lines without a quantity; `netAmount` is the
 * line's net amount as a string with 4 decimal places (negative on
 * cancellation documents). `expenseAccountNumber` is the account number the
 * line is booked on. `extras` holds the line's module data keyed by module
 * name (`IncomingInvoiceItemExtension::key()`), e.g. the fixed-asset
 * classification under `extras['fixed-assets']`; the core stores it unchanged.
 */
final readonly class IncomingInvoiceItemRef
{
    /**
     * @param  array<string, mixed>  $extras
     */
    public function __construct(
        public int $id,
        public int $incomingInvoiceId,
        public int $position,
        public string $description,
        public ?string $quantity,
        public string $netAmount,
        public ?string $expenseAccountNumber,
        public ?int $articleId,
        public array $extras = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'incoming_invoice_id' => $this->incomingInvoiceId,
            'position' => $this->position,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'net_amount' => $this->netAmount,
            'expense_account_number' => $this->expenseAccountNumber,
            'article_id' => $this->articleId,
            'extras' => $this->extras,
        ];
    }
}
