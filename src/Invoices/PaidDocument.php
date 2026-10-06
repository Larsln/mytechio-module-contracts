<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Lesender Verweis auf einen im Zeitraum bezahlten Beleg (Ausgangs- oder
 * Eingangsrechnung) für den Profit-Split.
 *
 * `type` ist `"invoice"` (aus `Invoices::paidBetween()`) oder
 * `"incoming_invoice"` (aus `Documents\IncomingInvoices::paidBetween()`);
 * `netAmount` ist der Netto-Betrag mit 4 Nachkommastellen, `url` die
 * relative Kern-URL des Belegs.
 */
final readonly class PaidDocument
{
    public function __construct(
        public string $type,
        public int $id,
        public string $number,
        public string $paidOn,
        public string $netAmount,
        public ?int $contactId,
        public string $url,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'number' => $this->number,
            'paid_on' => $this->paidOn,
            'net_amount' => $this->netAmount,
            'contact_id' => $this->contactId,
            'url' => $this->url,
        ];
    }
}
