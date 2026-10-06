<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Entwurf einer Rechnung, die ein Modul anlegen will.
 *
 * Kern-Semantik: Die Rechnung entsteht im Status Draft; Defaults
 * (Bankkonto, Zahlungsziel, Steuerkategorie aus Artikel bzw.
 * Organisations-Standard) ergänzt der Kern, wenn sie hier `null` bleiben.
 * Dieser Vertrag finalisiert niemals — das bleibt dem Kern-UI vorbehalten.
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
