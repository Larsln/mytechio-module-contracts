<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Shipping\CarrierData;
use MyTechIO\Contracts\Shipping\NewParcel;
use MyTechIO\Contracts\Shipping\NewShipment;
use MyTechIO\Contracts\Shipping\ParcelData;
use MyTechIO\Contracts\Shipping\ShipmentData;
use MyTechIO\Contracts\Shipping\ShipmentException;
use MyTechIO\Contracts\Shipping\ShipmentLabel;
use MyTechIO\Contracts\Shipping\Shipments;
use MyTechIO\Contracts\Shipping\ShipmentStatus;

/**
 * Test double for `Shipments`. `addCarrier()` declares the shipping
 * services (one that creates labels hands out the tracking numbers
 * `FAKE-<shipment>-<parcel>` and a PDF label), `setDefault()` the service
 * of a country. `failNext()` plays a carrier that refuses; `actors()` lists
 * who did what.
 */
final class FakeShipments implements Shipments
{
    /** @var array<int, CarrierData> */
    private array $carriers = [];

    /** @var array<string, int> */
    private array $defaults = [];

    /** @var array<int, ShipmentData> */
    private array $shipments = [];

    /** @var list<array{action: string, shipment: int, actor: ActorRef}> */
    private array $actors = [];

    private ?string $failure = null;

    private bool $printer = false;

    public function addCarrier(string $name, bool $createsLabels = false): CarrierData
    {
        $id = count($this->carriers) + 1;

        return $this->carriers[$id] = new CarrierData($id, $name, $createsLabels);
    }

    public function setDefault(string $country, int $carrierId): void
    {
        $this->defaults[strtoupper($country)] = $carrierId;
    }

    /** The next call that creates or cancels a shipment throws. */
    public function failNext(string $message = 'Der Paketdienst ist nicht erreichbar.'): void
    {
        $this->failure = $message;
    }

    /**
     * @return list<array{action: string, shipment: int, actor: ActorRef}>
     */
    public function actors(): array
    {
        return $this->actors;
    }

    public function carriers(): array
    {
        return array_values($this->carriers);
    }

    public function carrierFor(string $country): ?CarrierData
    {
        return $this->carriers[$this->defaults[strtoupper($country)] ?? 0] ?? null;
    }

    public function create(NewShipment $shipment, ActorRef $actor): ShipmentData
    {
        $this->failIfAsked();
        $carrier = $this->carriers[$shipment->carrierId] ?? throw new ShipmentException('Der Versanddienst wurde nicht gefunden.');

        if ($this->forOrder($shipment->orderId) !== null) {
            throw new ShipmentException('Zu diesem Auftrag gibt es bereits eine Sendung.');
        }

        if ($shipment->parcels === []) {
            throw new ShipmentException('Eine Sendung braucht mindestens ein Paket.');
        }

        $id = count($this->shipments) + 1;
        $parcels = [];

        foreach (array_values($shipment->parcels) as $index => $parcel) {
            if (! $parcel instanceof NewParcel || $parcel->weightGrams <= 0) {
                throw new ShipmentException('Jedes Paket braucht ein Gewicht.');
            }

            $position = $index + 1;
            $tracking = $carrier->createsLabels ? "FAKE-{$id}-{$position}" : ($parcel->trackingNumber === null || trim($parcel->trackingNumber) === '' ? null : trim($parcel->trackingNumber));
            $parcels[] = new ParcelData(
                $position, $parcel->weightGrams, $parcel->lengthMm, $parcel->widthMm, $parcel->heightMm, $tracking,
                $tracking === null ? null : 'https://track.example.test/'.rawurlencode($tracking), $carrier->createsLabels, $parcel->packaging,
            );
        }

        $this->actors[] = ['action' => 'create', 'shipment' => $id, 'actor' => $actor];

        return $this->shipments[$id] = new ShipmentData($id, $shipment->orderId, $carrier->id, $carrier->name, ShipmentStatus::Created, $shipment->recipient, $parcels);
    }

    public function find(int $shipmentId): ?ShipmentData
    {
        return $this->shipments[$shipmentId] ?? null;
    }

    public function forOrder(int $orderId): ?ShipmentData
    {
        foreach ($this->shipments as $shipment) {
            if ($shipment->orderId === $orderId && $shipment->status !== ShipmentStatus::Cancelled) {
                return $shipment;
            }
        }

        return null;
    }

    public function forOrders(array $orderIds): array
    {
        $found = [];

        foreach ($orderIds as $orderId) {
            $shipment = $this->forOrder($orderId);

            if ($shipment !== null) {
                $found[$orderId] = $shipment;
            }
        }

        return $found;
    }

    public function label(int $shipmentId, int $position): ?ShipmentLabel
    {
        foreach ($this->shipments[$shipmentId]->parcels ?? [] as $parcel) {
            if ($parcel->position === $position && $parcel->hasLabel) {
                return new ShipmentLabel("%PDF-1.4 fake label {$parcel->trackingNumber}", 'application/pdf', "label-{$parcel->trackingNumber}.pdf");
            }
        }

        return null;
    }

    public function printLabels(int $shipmentId, ActorRef $actor): int
    {
        $shipment = $this->active($shipmentId);
        $labels = count(array_filter($shipment->parcels, static fn (ParcelData $parcel): bool => $parcel->hasLabel));

        if (! $this->printer) {
            throw new ShipmentException('Es ist kein Etikettendrucker eingerichtet.');
        }

        if ($labels === 0) {
            throw new ShipmentException('Die Sendung hat keine Etiketten.');
        }

        $this->actors[] = ['action' => 'print', 'shipment' => $shipmentId, 'actor' => $actor];
        $this->shipments[$shipmentId] = new ShipmentData(
            $shipment->id, $shipment->orderId, $shipment->carrierId, $shipment->carrierName, $shipment->status, $shipment->recipient,
            array_map(static fn (ParcelData $parcel): ParcelData => new ParcelData(
                $parcel->position, $parcel->weightGrams, $parcel->lengthMm, $parcel->widthMm, $parcel->heightMm, $parcel->trackingNumber,
                $parcel->trackingUrl, $parcel->hasLabel, $parcel->packaging, $parcel->hasLabel ? 'queued' : null,
            ), $shipment->parcels),
            $shipment->shippedAt, $shipment->deliveredOn, $shipment->deliveryNote,
        );

        return $labels;
    }

    /** Declares that a label printer is set up, so `printLabels()` works. */
    public function withPrinter(bool $printer = true): void
    {
        $this->printer = $printer;
    }

    public function markShipped(int $shipmentId, ActorRef $actor): ShipmentData
    {
        $shipment = $this->active($shipmentId);

        if ($shipment->status !== ShipmentStatus::Created) {
            return $shipment;
        }

        $this->actors[] = ['action' => 'shipped', 'shipment' => $shipmentId, 'actor' => $actor];

        return $this->shipments[$shipmentId] = $this->with($shipment, ShipmentStatus::Shipped, '2030-01-02T10:00:00+00:00');
    }

    public function markDelivered(int $shipmentId, string $deliveredOn, ?string $note, ActorRef $actor): ShipmentData
    {
        $shipment = $this->active($shipmentId);
        $day = \DateTimeImmutable::createFromFormat('!Y-m-d', $deliveredOn);

        if ($day === false || $day->format('Y-m-d') !== $deliveredOn || $day > new \DateTimeImmutable('today')) {
            throw new ShipmentException('Das Zustelldatum ist kein gültiges Datum oder liegt in der Zukunft.');
        }

        $this->actors[] = ['action' => 'delivered', 'shipment' => $shipmentId, 'actor' => $actor];

        return $this->shipments[$shipmentId] = $this->with($shipment, ShipmentStatus::Delivered, $shipment->shippedAt ?? $deliveredOn.'T00:00:00+00:00', $deliveredOn, $note);
    }

    public function cancel(int $shipmentId, ActorRef $actor): ShipmentData
    {
        $this->failIfAsked();
        $shipment = $this->shipments[$shipmentId] ?? throw new ShipmentException('Die Sendung wurde nicht gefunden.');

        if ($shipment->status->isOnItsWay()) {
            throw new ShipmentException('Die Sendung ist bereits unterwegs.');
        }

        $this->actors[] = ['action' => 'cancel', 'shipment' => $shipmentId, 'actor' => $actor];

        return $this->shipments[$shipmentId] = $this->with($shipment, ShipmentStatus::Cancelled, null);
    }

    private function active(int $shipmentId): ShipmentData
    {
        $shipment = $this->shipments[$shipmentId] ?? throw new ShipmentException('Die Sendung wurde nicht gefunden.');

        if ($shipment->status === ShipmentStatus::Cancelled) {
            throw new ShipmentException('Die Sendung ist storniert.');
        }

        return $shipment;
    }

    private function with(ShipmentData $shipment, ShipmentStatus $status, ?string $shippedAt, ?string $deliveredOn = null, ?string $note = null): ShipmentData
    {
        return new ShipmentData($shipment->id, $shipment->orderId, $shipment->carrierId, $shipment->carrierName, $status, $shipment->recipient, $shipment->parcels, $shippedAt, $deliveredOn, $note);
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
