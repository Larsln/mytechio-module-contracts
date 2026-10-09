<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Invoices\RecurringBillingStatus;
use MyTechIO\Contracts\Invoices\RecurringInvoiceDraft;
use MyTechIO\Contracts\Invoices\RecurringInvoiceRef;
use MyTechIO\Contracts\Invoices\RecurringInvoices;
use MyTechIO\Contracts\Invoices\RecurringInvoiceSummary;
use MyTechIO\Contracts\Invoices\RecurringItemDraft;
use MyTechIO\Contracts\Invoices\RecurringItemSummary;

/**
 * Test double for `RecurringInvoices`: in-memory templates with auto IDs.
 * `seed()` sets the initial state, `seedBillingStatus()` the answer of
 * `billingStatus()` (zeros otherwise); `drafts()` and `endings()` record what
 * was created and ended.
 */
final class FakeRecurringInvoices implements RecurringInvoices
{
    /**
     * @var array<int, RecurringInvoiceSummary>
     */
    private array $templates = [];

    /**
     * @var array<int, RecurringBillingStatus>
     */
    private array $statuses = [];

    /**
     * @var array<int, RecurringInvoiceDraft>
     */
    private array $drafts = [];

    /**
     * @var list<array{id: int, endsOn: string, actor: ActorRef}>
     */
    private array $endings = [];

    private int $nextId = 1;

    private int $nextItemId = 1;

    public function seed(RecurringInvoiceSummary $summary): void
    {
        $this->templates[$summary->id] = $summary;
        $this->nextId = max($this->nextId, $summary->id + 1);

        foreach ($summary->items as $item) {
            $this->nextItemId = max($this->nextItemId, $item->id + 1);
        }
    }

    public function seedBillingStatus(RecurringBillingStatus $status): void
    {
        $this->statuses[$status->recurringInvoiceId] = $status;
    }

    public function forContact(int $contactId): array
    {
        return array_values(array_filter(
            $this->templates,
            static fn (RecurringInvoiceSummary $template): bool => $template->contactId === $contactId,
        ));
    }

    public function find(int $id): ?RecurringInvoiceSummary
    {
        return $this->templates[$id] ?? null;
    }

    public function create(RecurringInvoiceDraft $draft): RecurringInvoiceRef
    {
        $id = $this->nextId++;
        $this->drafts[$id] = $draft;

        $this->templates[$id] = new RecurringInvoiceSummary(
            id: $id,
            contactId: $draft->contactId,
            title: $draft->title,
            intervalUnit: $draft->intervalUnit,
            intervalCount: $draft->intervalCount,
            startDate: $draft->startDate,
            nextRunDate: $draft->startDate,
            endDate: null,
            isActive: true,
            items: $this->summarize($draft->items),
        );

        return new RecurringInvoiceRef($id);
    }

    public function addItems(int $id, array $items): RecurringInvoiceRef
    {
        $current = $this->templates[$id] ?? throw new ContractException("Recurring invoice {$id} not found.");

        $this->templates[$id] = new RecurringInvoiceSummary(
            id: $current->id,
            contactId: $current->contactId,
            title: $current->title,
            intervalUnit: $current->intervalUnit,
            intervalCount: $current->intervalCount,
            startDate: $current->startDate,
            nextRunDate: $current->nextRunDate,
            endDate: $current->endDate,
            isActive: $current->isActive,
            items: [...$current->items, ...$this->summarize($items)],
        );

        return new RecurringInvoiceRef($id);
    }

    public function endAt(int $id, string $endsOn, ActorRef $actor): void
    {
        $current = $this->templates[$id] ?? throw new ContractException("Recurring invoice {$id} not found.");

        $this->endings[] = ['id' => $id, 'endsOn' => $endsOn, 'actor' => $actor];

        $this->templates[$id] = new RecurringInvoiceSummary(
            id: $current->id,
            contactId: $current->contactId,
            title: $current->title,
            intervalUnit: $current->intervalUnit,
            intervalCount: $current->intervalCount,
            startDate: $current->startDate,
            nextRunDate: $current->nextRunDate !== null && $current->nextRunDate > $endsOn ? null : $current->nextRunDate,
            endDate: $endsOn,
            isActive: $current->isActive,
            items: $current->items,
        );
    }

    public function billingStatus(int $id): RecurringBillingStatus
    {
        $template = $this->templates[$id] ?? throw new ContractException("Recurring invoice {$id} not found.");

        return $this->statuses[$id] ?? new RecurringBillingStatus(
            recurringInvoiceId: $id,
            lastInvoicedPeriodEnd: null,
            nextRunDate: $template->nextRunDate,
            invoicedCount: 0,
            openInvoiceCount: 0,
            openAmountGross: '0.0000',
            oldestOpenDueDays: null,
        );
    }

    /**
     * @return array<int, RecurringInvoiceDraft>
     */
    public function drafts(): array
    {
        return $this->drafts;
    }

    /**
     * @return list<array{id: int, endsOn: string, actor: ActorRef}>
     */
    public function endings(): array
    {
        return $this->endings;
    }

    /**
     * @param  list<RecurringItemDraft>  $items
     * @return list<RecurringItemSummary>
     */
    private function summarize(array $items): array
    {
        return array_map(fn (RecurringItemDraft $item): RecurringItemSummary => new RecurringItemSummary(
            id: $this->nextItemId++,
            description: $item->description,
            quantity: $item->quantity,
            unitPriceNet: $item->unitPriceNet,
            extras: $item->extras,
        ), $items);
    }
}
