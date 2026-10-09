<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

use MyTechIO\Contracts\Accounting\ActorRef;

/**
 * Draft of a recurring-invoice template (Abo-Rechnung) that a module wants
 * to create.
 *
 * `intervalUnit` is `"month"` or `"year"`, `intervalCount` at least 1.
 * `startDate` (YYYY-MM-DD) is the first run date. The core fills defaults
 * (bank account, tax category, revenue account) when left `null`. The actor
 * is recorded in the audit log.
 */
final readonly class RecurringInvoiceDraft
{
    /**
     * @param  'month'|'year'  $intervalUnit
     * @param  list<RecurringItemDraft>  $items
     */
    public function __construct(
        public int $contactId,
        public string $title,
        public string $intervalUnit,
        public int $intervalCount,
        public string $startDate,
        public array $items,
        public ?int $paymentTermsDays,
        public ?string $introductionText,
        public ActorRef $actor,
    ) {
        Validate::interval($intervalUnit, $intervalCount);
        Validate::date($startDate, 'startDate');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'contact_id' => $this->contactId,
            'title' => $this->title,
            'interval_unit' => $this->intervalUnit,
            'interval_count' => $this->intervalCount,
            'start_date' => $this->startDate,
            'items' => array_map(static fn (RecurringItemDraft $item) => $item->toArray(), $this->items),
            'payment_terms_days' => $this->paymentTermsDays,
            'introduction_text' => $this->introductionText,
            'actor' => $this->actor->toArray(),
        ];
    }
}
