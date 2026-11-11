<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Payments;

/**
 * Card payments on a payment provider's hosted page (since 2.18), an
 * exclusive capability (`card_payments`) a payment module holds. A caller
 * (the shop) starts a payment, sends the customer to the page and asks on
 * the customer's return whether the amount is reserved. The amount is only
 * reserved (authorized) until `capture()` collects it (since 2.19);
 * collecting before a reservation runs out, releasing on a cancelled order
 * and refunding are the holder's own business.
 *
 * The holder also fires its own event when an amount was reserved without
 * the customer coming back, so a caller can finish the purchase anyway; the
 * holder's documentation names the event.
 *
 * Without a holder the contract is not bound: resolve it optionally.
 */
interface CardPayments
{
    /** Whether card payments can be taken right now (the provider is configured). */
    public function available(): bool;

    /**
     * Starts a payment and returns the page to send the customer to.
     *
     * @throws CardPaymentException when the provider refuses or cannot be reached, or the contact is unknown
     */
    public function authorize(NewCardPayment $payment): CardPaymentSession;

    /**
     * Asks the provider for the current state of the payment and stores it.
     *
     * @throws CardPaymentException when the payment is unknown or the provider cannot be reached
     */
    public function refresh(string $paymentId): CardPaymentData;

    /**
     * Notes the order the payment belongs to.
     *
     * @throws CardPaymentException when the payment is unknown
     */
    public function assignOrder(string $paymentId, int $orderId): CardPaymentData;

    /**
     * Releases a reserved amount that will not be collected (or closes a payment page that is still open).
     *
     * @throws CardPaymentException when the payment is unknown, already collected or the provider cannot be reached
     */
    public function release(string $paymentId): CardPaymentData;

    /**
     * Collects the reserved amount (since 2.19). `invoiceId` names the
     * invoice the money settles, so the holder can match the payout to it.
     * A payment that is already collected is returned as it is.
     *
     * @throws CardPaymentException when the payment is unknown, not reserved (open, released, run out) or the
     *                              provider refuses or cannot be reached
     */
    public function capture(string $paymentId, ?int $invoiceId = null): CardPaymentData;

    public function find(string $paymentId): ?CardPaymentData;

    public function findByReference(string $reference): ?CardPaymentData;

    public function forOrder(int $orderId): ?CardPaymentData;
}
