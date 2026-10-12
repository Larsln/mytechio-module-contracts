<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Orders;

/**
 * Input of `Orders::create()`: a standalone order draft (no quote). It is
 * either for a contact of the address book (`contactId`) or for a one-time
 * customer (`billingSnapshot` without a contact).
 *
 * A `billingSnapshot` needs `street`, `postal_code`, `city`, `country_code`
 * (ISO 3166-1 alpha-2), `email` and `type` (`b2b` or `b2c`); optional are
 * `company_name`, `first_name`, `last_name`, `house_number`, `vat_id`,
 * `business_proof_type` and `business_proof_reference`. `orderDate` and
 * `requestedDeliveryDate` are `Y-m-d`, the requested date may not precede the
 * order date. `skontoPercent` and `customerDiscountPercent` are decimal strings
 * with up to 2 decimals. The draft gets no number — `Orders::confirm()` draws
 * it.
 */
final readonly class NewOrder
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
