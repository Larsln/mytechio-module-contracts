<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Read-only reference to one line item of an incoming invoice.
 *
 * `quantity` is `null` for lines without a quantity; `netAmount` is the
 * line's net amount as a string with 4 decimal places (negative on
 * cancellation documents). `expenseAccountNumber` is the account number the
 * line is booked on. `assetKind` and `assetUsefulLifeYears` carry the
 * fixed-asset classification chosen on the capture form (`null` for ordinary
 * expense lines); the core stores them, the fixed-asset module acts on them.
 */
final readonly class IncomingInvoiceItemRef
{
    public function __construct(
        public int $id,
        public int $incomingInvoiceId,
        public int $position,
        public string $description,
        public ?string $quantity,
        public string $netAmount,
        public ?string $expenseAccountNumber,
        public ?int $articleId,
        public ?string $assetKind = null,
        public ?int $assetUsefulLifeYears = null,
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
            'asset_kind' => $this->assetKind,
            'asset_useful_life_years' => $this->assetUsefulLifeYears,
        ];
    }
}
