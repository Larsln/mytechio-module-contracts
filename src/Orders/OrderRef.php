<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

/**
 * Reference to an order as an `OrderHook` sees it. `number` is the order
 * number (`null` for a draft), `status` the core status value (`draft`,
 * `open`, `completed`, `cancelled`), `orderDate` is `Y-m-d`, `quoteId` names
 * the quote the order was created from. `items` are the lines in position
 * order and `url` is the core-relative path of the order's page.
 *
 * The appended fields complete the order (all optional so older callers keep
 * working). Amounts are decimal strings with 4 decimals: `netSubtotal` is the
 * sum of the lines, `customerDiscountPercent`/`customerDiscountAmount` the
 * order-wide discount, `netTotal`, `taxTotal` and `grossTotal` the totals after
 * it and `grandTotal` the payable amount. `vatMode` is `ist` or `soll`;
 * `taxScenario`/`taxScenarioNotes` the scenario the order is taxed with and
 * `taxCalculationMethod` `line_level` (private customers) or `top_down`
 * (businesses). `billingSnapshot` is the billing address as stored with the
 * order (it follows the contact only until the order is confirmed).
 * `invoiceIds` lists the invoices generated from the order (cancellation
 * documents included). A cancelled order carries `cancellationMode`
 * (`full_return`, `consumed` or `mixed`), `cancellationReason`, `cancelledBy`
 * (user ID) and `cancelledAt` (ISO 8601). `warnings` holds the German remarks
 * of the order hooks and is only filled on the result of `Orders::complete()`.
 */
final readonly class OrderRef
{
    /**
     * @param  list<OrderItemRef>  $items
     * @param  array<string, mixed>|null  $billingSnapshot
     * @param  list<int>  $invoiceIds
     * @param  list<string>  $warnings
     */
    public function __construct(
        public int $id,
        public ?string $number,
        public string $status,
        public ?int $contactId,
        public ?string $orderDate = null,
        public ?int $quoteId = null,
        public array $items = [],
        public ?string $url = null,
        public ?string $contactName = null,
        public ?string $requestedDeliveryDate = null,
        public ?string $projectTitle = null,
        public ?string $introductionText = null,
        public ?string $footerText = null,
        public ?string $internalNotes = null,
        public ?string $netSubtotal = null,
        public ?string $customerDiscountPercent = null,
        public ?string $customerDiscountAmount = null,
        public ?string $netTotal = null,
        public ?string $taxTotal = null,
        public ?string $grossTotal = null,
        public ?string $grandTotal = null,
        public ?string $vatMode = null,
        public ?string $taxScenario = null,
        public ?string $taxScenarioNotes = null,
        public ?string $taxCalculationMethod = null,
        public ?int $paymentTermsDays = null,
        public ?string $skontoPercent = null,
        public ?int $skontoDays = null,
        public ?int $bankAccountId = null,
        public ?array $billingSnapshot = null,
        public array $invoiceIds = [],
        public ?string $cancellationMode = null,
        public ?string $cancellationReason = null,
        public ?int $cancelledBy = null,
        public ?string $cancelledAt = null,
        public ?int $createdBy = null,
        public ?string $createdAt = null,
        public array $warnings = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'contact_id' => $this->contactId,
            'order_date' => $this->orderDate,
            'quote_id' => $this->quoteId,
            'items' => array_map(static fn (OrderItemRef $item): array => $item->toArray(), $this->items),
            'url' => $this->url,
            'contact_name' => $this->contactName,
            'requested_delivery_date' => $this->requestedDeliveryDate,
            'project_title' => $this->projectTitle,
            'introduction_text' => $this->introductionText,
            'footer_text' => $this->footerText,
            'internal_notes' => $this->internalNotes,
            'net_subtotal' => $this->netSubtotal,
            'customer_discount_percent' => $this->customerDiscountPercent,
            'customer_discount_amount' => $this->customerDiscountAmount,
            'net_total' => $this->netTotal,
            'tax_total' => $this->taxTotal,
            'gross_total' => $this->grossTotal,
            'grand_total' => $this->grandTotal,
            'vat_mode' => $this->vatMode,
            'tax_scenario' => $this->taxScenario,
            'tax_scenario_notes' => $this->taxScenarioNotes,
            'tax_calculation_method' => $this->taxCalculationMethod,
            'payment_terms_days' => $this->paymentTermsDays,
            'skonto_percent' => $this->skontoPercent,
            'skonto_days' => $this->skontoDays,
            'bank_account_id' => $this->bankAccountId,
            'billing_snapshot' => $this->billingSnapshot,
            'invoice_ids' => $this->invoiceIds,
            'cancellation_mode' => $this->cancellationMode,
            'cancellation_reason' => $this->cancellationReason,
            'cancelled_by' => $this->cancelledBy,
            'cancelled_at' => $this->cancelledAt,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'warnings' => $this->warnings,
        ];
    }
}
