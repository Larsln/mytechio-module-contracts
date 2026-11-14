<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Fakes\FakeReturnLabels;
use MyTechIO\Contracts\Fakes\FakeShipments;
use MyTechIO\Contracts\Shipping\NewParcel;
use MyTechIO\Contracts\Shipping\NewShipment;
use MyTechIO\Contracts\Shipping\ShipmentAddress;
use MyTechIO\Contracts\Shipping\ShipmentException;

beforeEach(function () {
    $this->shipments = new FakeShipments;
    $this->returns = new FakeReturnLabels($this->shipments);
    $this->actor = new ActorRef(1, 'Lars');
    $carrier = $this->shipments->addCarrier('GLS');
    $this->shipment = fn (int $orderId, string $country = 'DE') => $this->shipments->create(
        new NewShipment($orderId, $carrier->id, new ShipmentAddress('Erika Muster', 'Pontstraße 1', '52062', 'Aachen', $country), [new NewParcel(1000, trackingNumber: 'A'.$orderId)]),
        $this->actor,
    );
});

it('offers a label only for a priced country', function () {
    $this->returns->setOffer('de', '6.9');
    $this->returns->setOffer('AT', '14');
    $this->returns->setOffer('AT', null);

    expect($this->returns->offer('DE')?->toArray())->toBe(['country' => 'DE', 'price' => '6.90', 'currency' => 'EUR'])
        ->and($this->returns->offer('AT'))->toBeNull()
        ->and(array_keys($this->returns->offers()))->toBe(['DE']);
});

it('creates a label for a shipped shipment at the price of the day and hands out its file', function () {
    $this->returns->setOffer('DE', '6.90');
    $shipment = ($this->shipment)(1);

    expect(fn () => $this->returns->create($shipment->id, $this->actor))->toThrow(ShipmentException::class, 'versendete');

    $this->shipments->markShipped($shipment->id, $this->actor);
    $return = $this->returns->create($shipment->id, $this->actor);
    $this->returns->setOffer('DE', '9.90');

    expect($return->price)->toBe('6.90')
        ->and($return->orderId)->toBe(1)
        ->and($return->trackingNumber)->toBe('RETURN-1')
        ->and($return->toArray()['has_label'])->toBeTrue()
        ->and($this->returns->forShipment($shipment->id))->toHaveCount(1)
        ->and($this->returns->forShipment(99))->toBe([])
        ->and($this->returns->label($return->id)?->mimeType)->toBe('application/pdf')
        ->and($this->returns->label(99))->toBeNull()
        ->and($this->returns->created()[0]['actor'])->toBe($this->actor);

    $this->returns->purge($return->id);

    expect($this->returns->label($return->id))->toBeNull()
        ->and($this->returns->forShipment($shipment->id)[0]->hasLabel)->toBeFalse()
        ->and($this->returns->forShipment($shipment->id)[0]->price)->toBe('6.90');
});

it('refuses an unknown or cancelled shipment, a country without a price and a carrier that fails', function () {
    $this->returns->setOffer('DE', '6.90');
    $abroad = ($this->shipment)(2, 'FR');
    $this->shipments->markShipped($abroad->id, $this->actor);
    $cancelled = ($this->shipment)(3);
    $this->shipments->cancel($cancelled->id, $this->actor);
    $fine = ($this->shipment)(4);
    $this->shipments->markShipped($fine->id, $this->actor);

    expect(fn () => $this->returns->create(999, $this->actor))->toThrow(ShipmentException::class, 'nicht gefunden');

    $this->returns->failNext('DHL ist nicht erreichbar.');

    expect(fn () => $this->returns->create($fine->id, $this->actor))->toThrow(ShipmentException::class, 'DHL ist nicht erreichbar.')
        ->and(fn () => $this->returns->create($abroad->id, $this->actor))->toThrow(ShipmentException::class, 'kein Retourenlabel')
        ->and(fn () => $this->returns->create($cancelled->id, $this->actor))->toThrow(ShipmentException::class, 'versendete')
        ->and($this->returns->create($fine->id, $this->actor)->id)->toBe(1);
});
