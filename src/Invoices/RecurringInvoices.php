<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

use MyTechIO\Contracts\Accounting\ActorRef;

/**
 * Management of recurring-invoice templates (Abo-Rechnungen) from modules.
 *
 * Core semantics: templates never produce invoices through this contract —
 * the core's scheduled generator creates them. Amounts are 4-decimal strings,
 * dates YYYY-MM-DD. Invalid input or an unknown ID throws a
 * `ContractException`.
 */
interface RecurringInvoices
{
    /**
     * @return list<RecurringInvoiceSummary>
     */
    public function forContact(int $contactId): array;

    public function find(int $id): ?RecurringInvoiceSummary;

    /**
     * Creates a template. The contact must exist; tax category and revenue
     * account are resolved by code/number.
     */
    public function create(RecurringInvoiceDraft $draft): RecurringInvoiceRef;

    /**
     * Adds items to an existing template (same contact).
     *
     * @param  list<RecurringItemDraft>  $items
     */
    public function addItems(int $id, array $items): RecurringInvoiceRef;

    /**
     * Ends the template: no runs after `$endsOn` (YYYY-MM-DD).
     */
    public function endAt(int $id, string $endsOn, ActorRef $actor): void;

    public function billingStatus(int $id): RecurringBillingStatus;
}
