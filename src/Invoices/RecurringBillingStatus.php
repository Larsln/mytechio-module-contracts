<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Billing and payment state of a recurring-invoice template, derived from the
 * invoices generated from it.
 *
 * `lastInvoicedPeriodEnd` and `nextRunDate` are YYYY-MM-DD (`null` when
 * nothing has been invoiced yet / the template has ended). `openInvoiceCount`
 * and `openAmountGross` cover unpaid finalized invoices (`openAmountGross` is
 * a 4-decimal string); `oldestOpenDueDays` is how many days the oldest of them
 * is past its due date (`null` when none is open, 0 when not yet overdue).
 */
final readonly class RecurringBillingStatus
{
    public function __construct(
        public int $recurringInvoiceId,
        public ?string $lastInvoicedPeriodEnd,
        public ?string $nextRunDate,
        public int $invoicedCount,
        public int $openInvoiceCount,
        public string $openAmountGross,
        public ?int $oldestOpenDueDays,
    ) {
        Validate::decimal($openAmountGross, 'openAmountGross');

        if ($lastInvoicedPeriodEnd !== null) {
            Validate::date($lastInvoicedPeriodEnd, 'lastInvoicedPeriodEnd');
        }

        if ($nextRunDate !== null) {
            Validate::date($nextRunDate, 'nextRunDate');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'recurring_invoice_id' => $this->recurringInvoiceId,
            'last_invoiced_period_end' => $this->lastInvoicedPeriodEnd,
            'next_run_date' => $this->nextRunDate,
            'invoiced_count' => $this->invoicedCount,
            'open_invoice_count' => $this->openInvoiceCount,
            'open_amount_gross' => $this->openAmountGross,
            'oldest_open_due_days' => $this->oldestOpenDueDays,
        ];
    }
}
