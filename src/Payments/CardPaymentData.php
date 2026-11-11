<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Payments;

/**
 * A card payment as its holder knows it. `amount` is a decimal string with
 * two decimals; `authorizedAt` and `captureBefore` (the reservation runs
 * out then) are ISO 8601 and null until the amount is reserved.
 */
final readonly class CardPaymentData
{
    public function __construct(
        public string $paymentId,
        public string $reference,
        public int $contactId,
        public CardPaymentStatus $status,
        public string $amount,
        public string $currency,
        public ?int $orderId = null,
        public ?string $authorizedAt = null,
        public ?string $captureBefore = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'payment_id' => $this->paymentId,
            'reference' => $this->reference,
            'contact_id' => $this->contactId,
            'status' => $this->status->value,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'order_id' => $this->orderId,
            'authorized_at' => $this->authorizedAt,
            'capture_before' => $this->captureBefore,
        ];
    }
}
