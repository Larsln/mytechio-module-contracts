<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Shipping\ReturnLabelData;
use MyTechIO\Contracts\Shipping\ReturnLabels;
use MyTechIO\Contracts\Shipping\ReturnOffer;
use MyTechIO\Contracts\Shipping\ShipmentException;
use MyTechIO\Contracts\Shipping\ShipmentLabel;
use MyTechIO\Contracts\Shipping\Shipments;
use MyTechIO\Contracts\Shipping\ShipmentStatus;

/**
 * Test double for `ReturnLabels`. It reads the shipments from the given
 * `Shipments` (usually a `FakeShipments`); `setOffer()` prices a country,
 * `failNext()` plays a carrier that refuses, `created()` lists who created
 * which label and `purge()` plays the holder deleting a file.
 */
final class FakeReturnLabels implements ReturnLabels
{
    /** @var array<string, ReturnOffer> */
    private array $offers = [];

    /** @var array<int, ReturnLabelData> */
    private array $returns = [];

    /** @var list<array{return: int, shipment: int, actor: ActorRef}> */
    private array $created = [];

    private ?string $failure = null;

    public function __construct(private readonly Shipments $shipments) {}

    public function setOffer(string $country, ?string $price): void
    {
        $country = strtoupper($country);

        if ($price === null) {
            unset($this->offers[$country]);

            return;
        }

        $this->offers[$country] = new ReturnOffer($country, number_format((float) $price, 2, '.', ''));
    }

    public function offer(string $country): ?ReturnOffer
    {
        return $this->offers[strtoupper($country)] ?? null;
    }

    public function offers(): array
    {
        return $this->offers;
    }

    public function create(int $shipmentId, ActorRef $actor): ReturnLabelData
    {
        if ($this->failure !== null) {
            $message = $this->failure;
            $this->failure = null;

            throw new ShipmentException($message);
        }

        $shipment = $this->shipments->find($shipmentId) ?? throw new ShipmentException('Die Sendung wurde nicht gefunden.');

        if ($shipment->status === ShipmentStatus::Cancelled || ! $shipment->status->isOnItsWay()) {
            throw new ShipmentException('Ein Retourenlabel gibt es erst für eine versendete Sendung.');
        }

        $offer = $this->offer($shipment->recipient->country) ?? throw new ShipmentException('Für dieses Land wird kein Retourenlabel angeboten.');
        $id = count($this->returns) + 1;
        $number = "RETURN-{$id}";
        $this->created[] = ['return' => $id, 'shipment' => $shipmentId, 'actor' => $actor];

        return $this->returns[$id] = new ReturnLabelData(
            $id, $shipmentId, $shipment->orderId, $offer->country, $number, 'https://track.example.test/'.$number, $offer->price, '2030-01-03T10:00:00+00:00',
        );
    }

    public function forShipment(int $shipmentId): array
    {
        return array_values(array_filter($this->returns, static fn (ReturnLabelData $return): bool => $return->shipmentId === $shipmentId));
    }

    public function label(int $returnId): ?ShipmentLabel
    {
        $return = $this->returns[$returnId] ?? null;

        return $return === null || ! $return->hasLabel ? null : new ShipmentLabel("%PDF-FAKE {$return->trackingNumber}", 'application/pdf', "retourenlabel-{$returnId}.pdf");
    }

    /** The next `create()` is refused with this message. */
    public function failNext(string $message = 'Der Paketdienst ist nicht erreichbar.'): void
    {
        $this->failure = $message;
    }

    /** The holder deleted the file of this label. */
    public function purge(int $returnId): void
    {
        $return = $this->returns[$returnId] ?? null;

        if ($return !== null) {
            $this->returns[$returnId] = new ReturnLabelData(
                $return->id, $return->shipmentId, $return->orderId, $return->country, $return->trackingNumber, $return->trackingUrl, $return->price,
                $return->createdAt, false, $return->currency,
            );
        }
    }

    /**
     * @return list<array{return: int, shipment: int, actor: ActorRef}>
     */
    public function created(): array
    {
        return $this->created;
    }
}
