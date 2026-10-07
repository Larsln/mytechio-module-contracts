<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Draft of an invoice that a module wants to create.
 *
 * Core semantics: the invoice is created in draft status; defaults (bank
 * account, payment terms, tax category derived from the article or the
 * organization default) are filled in by the core when left `null` here.
 * This contract never finalizes an invoice — that remains the sole
 * responsibility of the core UI.
 */
final readonly class InvoiceDraft
{
    /**
     * @param  list<InvoiceDraftItem>  $items
     */
    public function __construct(
        public int $contactId,
        public array $items,
        public ?int $paymentTermsDays = null,
        public ?int $bankAccountId = null,
        public ?string $invoiceDate = null,
        public ?string $introductionText = null,
        public ?string $footerText = null,
        public ?string $projectTitle = null,
        public ?string $internalNotes = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'contact_id' => $this->contactId,
            'items' => array_map(static fn (InvoiceDraftItem $item) => $item->toArray(), $this->items),
            'payment_terms_days' => $this->paymentTermsDays,
            'bank_account_id' => $this->bankAccountId,
            'invoice_date' => $this->invoiceDate,
            'introduction_text' => $this->introductionText,
            'footer_text' => $this->footerText,
            'project_title' => $this->projectTitle,
            'internal_notes' => $this->internalNotes,
        ];
    }
}
