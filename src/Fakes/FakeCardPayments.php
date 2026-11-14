<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Payments\CardPaymentData;
use MyTechIO\Contracts\Payments\CardPaymentException;
use MyTechIO\Contracts\Payments\CardPayments;
use MyTechIO\Contracts\Payments\CardPaymentSession;
use MyTechIO\Contracts\Payments\CardPaymentStatus;
use MyTechIO\Contracts\Payments\NewCardPayment;

/**
 * Test double for `CardPayments`. Payments get the ids `pay_1`, `pay_2`, …
 * and start `Open`; the test plays the provider with `settle()` (what the
 * next `refresh()` will find). `capture()` collects a reserved payment (`captured()` lists them);
 * `unavailable()` and `failNext()` simulate a
 * provider that is off or refuses; `started()` lists what was asked for.
 */
final class FakeCardPayments implements CardPayments
{
    private bool $available = true;

    private ?string $failure = null;

    /** @var array<string, CardPaymentData> */
    private array $payments = [];

    /** @var array<string, CardPaymentStatus> what the provider knows and the next refresh() stores */
    private array $provider = [];

    /** @var list<NewCardPayment> */
    private array $started = [];

    /** @var list<string> */
    private array $released = [];

    /** @var array<string, int|null> */
    private array $captured = [];

    public function unavailable(): void
    {
        $this->available = false;
    }

    /** The next call that talks to the provider throws. */
    public function failNext(string $message = 'Der Zahlungsanbieter ist nicht erreichbar.'): void
    {
        $this->failure = $message;
    }

    /** What the provider will answer on the next `refresh()` of the payment. */
    public function settle(string $paymentId, CardPaymentStatus $status): void
    {
        $this->provider[$paymentId] = $status;
    }

    /**
     * @return list<NewCardPayment>
     */
    public function started(): array
    {
        return $this->started;
    }

    /**
     * @return list<string> ids of released payments
     */
    public function released(): array
    {
        return $this->released;
    }

    public function available(): bool
    {
        return $this->available;
    }

    public function authorize(NewCardPayment $payment): CardPaymentSession
    {
        $this->failIfAsked();

        if (! $this->available) {
            throw new CardPaymentException('Kartenzahlung ist nicht eingerichtet.');
        }

        $id = 'pay_'.(count($this->payments) + 1);
        $this->started[] = $payment;
        $this->payments[$id] = new CardPaymentData($id, $payment->reference, $payment->contactId, CardPaymentStatus::Open, bcadd($payment->amount, '0', 2), 'EUR');
        $this->provider[$id] = CardPaymentStatus::Open;

        return new CardPaymentSession($id, 'https://pay.example.test/'.$id, '2030-01-01T00:30:00+00:00');
    }

    public function refresh(string $paymentId): CardPaymentData
    {
        $this->failIfAsked();
        $payment = $this->get($paymentId);
        $status = $this->provider[$paymentId] ?? $payment->status;
        $secured = $status->isSecured();

        return $this->payments[$paymentId] = new CardPaymentData(
            $payment->paymentId, $payment->reference, $payment->contactId, $status, $payment->amount, $payment->currency, $payment->orderId,
            $secured ? ($payment->authorizedAt ?? '2030-01-01T00:05:00+00:00') : null,
            $secured ? ($payment->captureBefore ?? '2030-01-08T00:05:00+00:00') : null,
        );
    }

    public function assignOrder(string $paymentId, int $orderId): CardPaymentData
    {
        $payment = $this->get($paymentId);

        return $this->payments[$paymentId] = new CardPaymentData($payment->paymentId, $payment->reference, $payment->contactId, $payment->status, $payment->amount, $payment->currency, $orderId, $payment->authorizedAt, $payment->captureBefore);
    }

    public function release(string $paymentId): CardPaymentData
    {
        $this->failIfAsked();
        $payment = $this->get($paymentId);

        if ($payment->status === CardPaymentStatus::Captured) {
            throw new CardPaymentException('Die Zahlung ist bereits eingezogen.');
        }

        $this->released[] = $paymentId;
        $this->provider[$paymentId] = CardPaymentStatus::Canceled;

        return $this->payments[$paymentId] = new CardPaymentData($payment->paymentId, $payment->reference, $payment->contactId, CardPaymentStatus::Canceled, $payment->amount, $payment->currency, $payment->orderId);
    }

    public function capture(string $paymentId, ?int $invoiceId = null): CardPaymentData
    {
        $this->failIfAsked();
        $payment = $this->get($paymentId);
        $status = $this->provider[$paymentId] ?? $payment->status;

        if ($status === CardPaymentStatus::Captured) {
            return $this->refresh($paymentId);
        }

        if ($status !== CardPaymentStatus::Authorized) {
            throw new CardPaymentException('Der Betrag ist nicht reserviert und kann nicht eingezogen werden.');
        }

        $this->captured[$paymentId] = $invoiceId;
        $this->provider[$paymentId] = CardPaymentStatus::Captured;

        return $this->refresh($paymentId);
    }

    /**
     * @return array<string, int|null> the invoice each collected payment was collected for, by payment id
     */
    public function captured(): array
    {
        return $this->captured;
    }

    public function find(string $paymentId): ?CardPaymentData
    {
        return $this->payments[$paymentId] ?? null;
    }

    public function findByReference(string $reference): ?CardPaymentData
    {
        foreach ($this->payments as $payment) {
            if ($payment->reference === $reference) {
                return $payment;
            }
        }

        return null;
    }

    public function forOrder(int $orderId): ?CardPaymentData
    {
        foreach ($this->payments as $payment) {
            if ($payment->orderId === $orderId) {
                return $payment;
            }
        }

        return null;
    }

    private function get(string $paymentId): CardPaymentData
    {
        return $this->payments[$paymentId] ?? throw new CardPaymentException("Zahlung {$paymentId} wurde nicht gefunden.");
    }

    private function failIfAsked(): void
    {
        if ($this->failure !== null) {
            $message = $this->failure;
            $this->failure = null;

            throw new CardPaymentException($message);
        }
    }
}
