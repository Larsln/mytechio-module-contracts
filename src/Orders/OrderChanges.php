<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

/**
 * Input of `Orders::update()`: the new state of an order DRAFT. Like the
 * order form it is saved as a whole — every field is replaced and `items`
 * fully replace the previous lines (tax scenario and totals are re-derived
 * from the current master data). Same fields and rules as `NewOrder`.
 */
final readonly class OrderChanges
{
    /**
     * @param  list<NewOrderItem>  $items
     * @param  array<string, mixed>|null  $billingSnapshot
     */
    public function __construct(
        public ?int $contactId,
        public string $orderDate,
        public int $paymentTermsDays,
        public array $items,
        public ?array $billingSnapshot = null,
        public ?string $requestedDeliveryDate = null,
        public ?string $projectTitle = null,
        public ?string $introductionText = null,
        public ?string $footerText = null,
        public ?string $internalNotes = null,
        public ?string $skontoPercent = null,
        public ?int $skontoDays = null,
        public string $customerDiscountPercent = '0',
        public ?int $bankAccountId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'contact_id' => $this->contactId,
            'order_date' => $this->orderDate,
            'payment_terms_days' => $this->paymentTermsDays,
            'items' => array_map(static fn (NewOrderItem $item): array => $item->toArray(), $this->items),
            'billing_snapshot' => $this->billingSnapshot,
            'requested_delivery_date' => $this->requestedDeliveryDate,
            'project_title' => $this->projectTitle,
            'introduction_text' => $this->introductionText,
            'footer_text' => $this->footerText,
            'internal_notes' => $this->internalNotes,
            'skonto_percent' => $this->skontoPercent,
            'skonto_days' => $this->skontoDays,
            'customer_discount_percent' => $this->customerDiscountPercent,
            'bank_account_id' => $this->bankAccountId,
        ];
    }
}
