<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeShipments;
use MyTechIO\Contracts\Shipping\NewParcel;
use MyTechIO\Contracts\Shipping\NewShipment;
use MyTechIO\Contracts\Shipping\ShipmentAddress;
use MyTechIO\Contracts\Shipping\ShipmentException;
use MyTechIO\Contracts\Shipping\Shipments;
use MyTechIO\Contracts\Shipping\ShipmentStatus;

function shipmentTo(int $orderId, int $carrierId, array $parcels): NewShipment
{
    return new NewShipment($orderId, $carrierId, new ShipmentAddress('Kira Kasse', 'Pontstraße 1', '52062', 'Aachen', 'DE'), $parcels, 'AB-2026-00001');
}

beforeEach(function () {
    $this->shipments = new FakeShipments;
    $this->actor = new ActorRef(3, 'Lars');
});

it('knows the shipping services and the one preselected for a country', function () {
    $manual = $this->shipments->addCarrier('GLS');
    $dhl = $this->shipments->addCarrier('DHL', createsLabels: true);
    $this->shipments->setDefault('de', $dhl->id);

    expect($this->shipments)->toBeInstanceOf(Shipments::class)
        ->and(array_map(fn ($carrier) => $carrier->toArray(), $this->shipments->carriers()))->toBe([
            ['id' => 1, 'name' => 'GLS', 'creates_labels' => false, 'public_name' => null],
            ['id' => 2, 'name' => 'DHL', 'creates_labels' => true, 'public_name' => null],
        ])
        ->and($this->shipments->carrierFor('DE')?->id)->toBe($dhl->id)
        ->and($this->shipments->carrierFor('AT'))->toBeNull()
        ->and($manual->createsLabels)->toBeFalse();
});

it('keeps the typed tracking numbers of a service without an interface and has no label', function () {
    $carrier = $this->shipments->addCarrier('GLS');
    $shipment = $this->shipments->create(shipmentTo(55, $carrier->id, [new NewParcel(1200, 300, 200, 100, ' 123456 ', 'Karton S'), new NewParcel(800)]), $this->actor);

    expect($shipment->status)->toBe(ShipmentStatus::Created)
        ->and($shipment->carrierName)->toBe('GLS')
        ->and($shipment->parcels[0]->toArray())->toBe([
            'position' => 1, 'weight_grams' => 1200, 'length_mm' => 300, 'width_mm' => 200, 'height_mm' => 100,
            'tracking_number' => '123456', 'tracking_url' => 'https://track.example.test/123456', 'has_label' => false, 'packaging' => 'Karton S', 'print_state' => null,
            'tracking_status' => null, 'tracking_text' => null, 'tracking_at' => null,
        ])
        ->and($shipment->parcels[1]->trackingNumber)->toBeNull()
        ->and($this->shipments->label($shipment->id, 1))->toBeNull()
        ->and($this->shipments->forOrder(55)?->id)->toBe($shipment->id)
        ->and($this->shipments->forOrders([55, 56]))->toHaveKeys([55])->not->toHaveKey(56)
        ->and($shipment->toArray()['recipient']['city'])->toBe('Aachen');
});

it('creates tracking numbers and labels for a service with an interface', function () {
    $carrier = $this->shipments->addCarrier('DHL', createsLabels: true);
    $shipment = $this->shipments->create(shipmentTo(55, $carrier->id, [new NewParcel(1200), new NewParcel(900)]), $this->actor);

    expect($shipment->parcels[1]->trackingNumber)->toBe('FAKE-1-2')
        ->and($shipment->parcels[1]->hasLabel)->toBeTrue()
        ->and($this->shipments->label(1, 2)?->mimeType)->toBe('application/pdf')
        ->and($this->shipments->label(1, 3))->toBeNull()
        ->and($this->shipments->label(9, 1))->toBeNull();
});

it('refuses an unknown service, a second shipment of an order, no parcels and a parcel without weight', function () {
    $carrier = $this->shipments->addCarrier('GLS');
    $this->shipments->create(shipmentTo(55, $carrier->id, [new NewParcel(500)]), $this->actor);

    expect(fn () => $this->shipments->create(shipmentTo(56, 99, [new NewParcel(500)]), $this->actor))->toThrow(ShipmentException::class, 'Versanddienst')
        ->and(fn () => $this->shipments->create(shipmentTo(55, $carrier->id, [new NewParcel(500)]), $this->actor))->toThrow(ShipmentException::class, 'bereits eine Sendung')
        ->and(fn () => $this->shipments->create(shipmentTo(56, $carrier->id, []), $this->actor))->toThrow(ShipmentException::class, 'mindestens ein Paket')
        ->and(fn () => $this->shipments->create(shipmentTo(56, $carrier->id, [new NewParcel(0)]), $this->actor))->toThrow(ContractException::class, 'Gewicht');
});

it('follows a shipment from shipped to delivered and remembers who did it', function () {
    $carrier = $this->shipments->addCarrier('GLS');
    $id = $this->shipments->create(shipmentTo(55, $carrier->id, [new NewParcel(500)]), $this->actor)->id;

    $shipped = $this->shipments->markShipped($id, $this->actor);
    $again = $this->shipments->markShipped($id, $this->actor);
    $delivered = $this->shipments->markDelivered($id, '2026-11-09', 'Unterschrift Kasse', $this->actor);

    expect($shipped->status)->toBe(ShipmentStatus::Shipped)
        ->and($shipped->status->isOnItsWay())->toBeTrue()
        ->and($again->shippedAt)->toBe($shipped->shippedAt)
        ->and($delivered->status)->toBe(ShipmentStatus::Delivered)
        ->and($delivered->deliveredOn)->toBe('2026-11-09')
        ->and($delivered->deliveryNote)->toBe('Unterschrift Kasse')
        ->and($delivered->shippedAt)->toBe($shipped->shippedAt)
        ->and(array_column($this->shipments->actors(), 'action'))->toBe(['create', 'shipped', 'delivered'])
        ->and(fn () => $this->shipments->markDelivered($id, '2999-01-01', null, $this->actor))->toThrow(ShipmentException::class, 'Zukunft')
        ->and(fn () => $this->shipments->markDelivered($id, '12.11.2026', null, $this->actor))->toThrow(ShipmentException::class)
        ->and(fn () => $this->shipments->cancel($id, $this->actor))->toThrow(ShipmentException::class, 'unterwegs');
});

it('counts a shipment that is marked delivered without having been marked shipped as shipped', function () {
    $carrier = $this->shipments->addCarrier('GLS');
    $id = $this->shipments->create(shipmentTo(55, $carrier->id, [new NewParcel(500)]), $this->actor)->id;

    expect($this->shipments->markDelivered($id, '2026-11-09', null, $this->actor)->shippedAt)->toBe('2026-11-09T00:00:00+00:00');
});

it('cancels a shipment that did not leave, frees the order for a new one and refuses to move a cancelled one', function () {
    $carrier = $this->shipments->addCarrier('GLS');
    $id = $this->shipments->create(shipmentTo(55, $carrier->id, [new NewParcel(500)]), $this->actor)->id;

    expect($this->shipments->cancel($id, $this->actor)->status)->toBe(ShipmentStatus::Cancelled)
        ->and($this->shipments->forOrder(55))->toBeNull()
        ->and($this->shipments->find($id)?->status)->toBe(ShipmentStatus::Cancelled)
        ->and(fn () => $this->shipments->markShipped($id, $this->actor))->toThrow(ShipmentException::class, 'storniert')
        ->and($this->shipments->create(shipmentTo(55, $carrier->id, [new NewParcel(500)]), $this->actor)->id)->toBe(2);
});

it('plays a carrier that refuses once', function () {
    $carrier = $this->shipments->addCarrier('DHL', createsLabels: true);
    $this->shipments->failNext('DHL lehnt die Adresse ab.');

    expect(fn () => $this->shipments->create(shipmentTo(55, $carrier->id, [new NewParcel(500)]), $this->actor))->toThrow(ShipmentException::class, 'DHL lehnt die Adresse ab.')
        ->and($this->shipments->create(shipmentTo(55, $carrier->id, [new NewParcel(500)]), $this->actor)->id)->toBe(1);
});

it('sends the labels to the printer only when one is set up and the shipment has labels', function () {
    $dhl = $this->shipments->addCarrier('DHL', createsLabels: true);
    $gls = $this->shipments->addCarrier('GLS');
    $withLabels = $this->shipments->create(shipmentTo(55, $dhl->id, [new NewParcel(500), new NewParcel(700)]), $this->actor)->id;
    $without = $this->shipments->create(shipmentTo(56, $gls->id, [new NewParcel(500)]), $this->actor)->id;

    expect(fn () => $this->shipments->printLabels($withLabels, $this->actor))->toThrow(ShipmentException::class, 'kein Etikettendrucker');

    $this->shipments->withPrinter();

    expect($this->shipments->printLabels($withLabels, $this->actor))->toBe(2)
        ->and($this->shipments->find($withLabels)->parcels[1]->printState)->toBe('queued')
        ->and(fn () => $this->shipments->printLabels($without, $this->actor))->toThrow(ShipmentException::class, 'keine Etiketten')
        ->and(fn () => $this->shipments->printLabels(99, $this->actor))->toThrow(ShipmentException::class);
});

it('keeps a carrier name for customers apart from the one staff uses', function () {
    $shipments = new FakeShipments;
    $actor = new ActorRef(1, 'Lars');
    $plain = $shipments->addCarrier('GLS');
    $named = $shipments->addCarrier('DHL (Schnittstelle)', true, 'DHL');
    $to = new ShipmentAddress('Erika Muster', 'Pontstraße 1', '52062', 'Aachen', 'DE');

    $first = $shipments->create(new NewShipment(1, $plain->id, $to, [new NewParcel(1000, trackingNumber: 'A1')]), $actor);
    $second = $shipments->create(new NewShipment(2, $named->id, $to, [new NewParcel(1000)]), $actor);

    $shipments->withPrinter();

    expect($first->carrierPublicName)->toBeNull()
        ->and($first->customerCarrierName())->toBe('GLS')
        ->and($second->carrierName)->toBe('DHL (Schnittstelle)')
        ->and($second->customerCarrierName())->toBe('DHL')
        ->and($second->toArray()['carrier_public_name'])->toBe('DHL')
        // The name survives printing and the states after it.
        ->and($shipments->printLabels($second->id, $actor))->toBeGreaterThan(0)
        ->and($shipments->markShipped($second->id, $actor)->customerCarrierName())->toBe('DHL')
        ->and($shipments->find($second->id)?->customerCarrierName())->toBe('DHL');
});
