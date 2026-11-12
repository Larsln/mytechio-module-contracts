<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Shipping\Carriers\CarrierDriver;
use MyTechIO\Contracts\Shipping\Carriers\CreatesReturnLabels;
use MyTechIO\Contracts\Shipping\Carriers\DriverParcel;
use MyTechIO\Contracts\Shipping\Carriers\ReturnLabelFile;
use MyTechIO\Contracts\Shipping\Carriers\TrackingState;
use MyTechIO\Contracts\Shipping\Carriers\TracksParcels;
use MyTechIO\Contracts\Shipping\NewShipment;
use MyTechIO\Contracts\Shipping\ShipmentAddress;
use MyTechIO\Contracts\Shipping\ShipmentException;

/**
 * Test double for a carrier driver that creates labels, tracks parcels
 * and creates return labels. Booked parcels get the numbers
 * `<KEY>-1`, `<KEY>-2`, … and a small PDF label. `ready()` plays a
 * missing access, `failNext()` a carrier that refuses, `says()` what the
 * carrier answers about a parcel, `trackingOn()` and `budget()` the
 * tracking switch and what is left of the day's questions.
 */
final class FakeCarrierDriver implements CarrierDriver, CreatesReturnLabels, TracksParcels
{
    private bool $ready = true;

    private bool $tracking = true;

    private int $budget = 100;

    private ?string $failure = null;

    private int $counter = 0;

    /** @var list<NewShipment> */
    private array $booked = [];

    /** @var list<list<string>> */
    private array $cancelled = [];

    /** @var array<string, TrackingState|null> */
    private array $says = [];

    /** @var list<string> */
    private array $asked = [];

    /** @var list<array{sender: ShipmentAddress, receiver_id: string, reference: string|null, weight_grams: int|null}> */
    private array $returns = [];

    public function __construct(private readonly string $key = 'fake', private readonly string $label = 'Testdienst (Schnittstelle)') {}

    public function ready(bool $ready = true): void
    {
        $this->ready = $ready;
    }

    public function trackingOn(bool $on = true): void
    {
        $this->tracking = $on;
    }

    public function budget(int $calls): void
    {
        $this->budget = max(0, $calls);
    }

    /** The next call to the carrier (booking, cancelling, tracking, return label) fails with this message. */
    public function failNext(string $message = 'Der Paketdienst ist nicht erreichbar.'): void
    {
        $this->failure = $message;
    }

    /** What the carrier answers about a tracking number from now on; null = it does not know the number. */
    public function says(string $trackingNumber, ?TrackingState $state): void
    {
        $this->says[$trackingNumber] = $state;
    }

    /** @return list<NewShipment> */
    public function booked(): array
    {
        return $this->booked;
    }

    /** @return list<list<string>> */
    public function cancelled(): array
    {
        return $this->cancelled;
    }

    /** @return list<string> the tracking numbers asked about, in order */
    public function asked(): array
    {
        return $this->asked;
    }

    /** @return list<array{sender: ShipmentAddress, receiver_id: string, reference: string|null, weight_grams: int|null}> */
    public function returns(): array
    {
        return $this->returns;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function createsLabels(): bool
    {
        return $this->ready;
    }

    public function createShipment(NewShipment $shipment): array
    {
        $this->failIfAsked();
        $this->booked[] = $shipment;
        $parcels = [];

        foreach ($shipment->parcels as $ignored) {
            $number = strtoupper($this->key).'-'.(++$this->counter);
            $parcels[] = new DriverParcel($number, $number, base64_encode("%PDF-1.4\n% label {$number}\n"), 'application/pdf');
        }

        return $parcels;
    }

    public function cancelShipment(array $references): void
    {
        $this->failIfAsked();
        $this->cancelled[] = array_values($references);
    }

    public function tracksCarrier(string $driverKey, ?string $trackingUrlTemplate): bool
    {
        return $this->tracking && $driverKey === $this->key;
    }

    public function trackingBudget(): int
    {
        return $this->tracking ? $this->budget : 0;
    }

    public function trackingState(string $trackingNumber): ?TrackingState
    {
        if ($this->budget === 0) {
            throw new ShipmentException('Für heute sind die Abfragen der Sendungsverfolgung aufgebraucht.');
        }

        $this->failIfAsked();
        $this->budget--;
        $this->asked[] = $trackingNumber;

        return $this->says[$trackingNumber] ?? null;
    }

    public function returnsReady(): bool
    {
        return $this->ready;
    }

    public function createReturnLabel(ShipmentAddress $sender, string $receiverId, ?string $reference, ?int $weightGrams): ReturnLabelFile
    {
        if (! $this->ready) {
            throw new ShipmentException('Der Zugang des Paketdienstes ist unvollständig.');
        }

        $this->failIfAsked();
        $this->returns[] = ['sender' => $sender, 'receiver_id' => $receiverId, 'reference' => $reference, 'weight_grams' => $weightGrams];
        $number = 'RETURN-'.count($this->returns);

        return new ReturnLabelFile($number, "%PDF-1.4\n% return {$number}\n", 'application/pdf', 'https://track.example.test/'.$number);
    }

    private function failIfAsked(): void
    {
        if ($this->failure !== null) {
            $message = $this->failure;
            $this->failure = null;

            throw new ShipmentException($message);
        }
    }
}
