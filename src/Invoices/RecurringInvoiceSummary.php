<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Read model of a recurring-invoice template. Dates are YYYY-MM-DD.
 * `nextRunDate` is `null` once the template has ended, `endDate` is `null`
 * for an open-ended template, `isActive` is `false` for paused or ended
 * templates.
 */
final readonly class RecurringInvoiceSummary
{
    /**
     * @param  list<RecurringItemSummary>  $items
     */
    public function __construct(
        public int $id,
        public int $contactId,
        public string $title,
        public string $intervalUnit,
        public int $intervalCount,
        public string $startDate,
        public ?string $nextRunDate,
        public ?string $endDate,
        public bool $isActive,
        public array $items = [],
    ) {
        Validate::interval($intervalUnit, $intervalCount);
        Validate::date($startDate, 'startDate');

        if ($nextRunDate !== null) {
            Validate::date($nextRunDate, 'nextRunDate');
        }

        if ($endDate !== null) {
            Validate::date($endDate, 'endDate');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'contact_id' => $this->contactId,
            'title' => $this->title,
            'interval_unit' => $this->intervalUnit,
            'interval_count' => $this->intervalCount,
            'start_date' => $this->startDate,
            'next_run_date' => $this->nextRunDate,
            'end_date' => $this->endDate,
            'is_active' => $this->isActive,
            'items' => array_map(static fn (RecurringItemSummary $item) => $item->toArray(), $this->items),
        ];
    }
}
