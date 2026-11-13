<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Fakes\FakePickupPoints;
use MyTechIO\Contracts\Fakes\FakeShipments;
use MyTechIO\Contracts\Shipping\NewParcel;
use MyTechIO\Contracts\Shipping\NewShipment;
use MyTechIO\Contracts\Shipping\PickupPoint;
use MyTechIO\Contracts\Shipping\ShipmentAddress;
use MyTechIO\Contracts\Shipping\ShipmentException;
use MyTechIO\Contracts\Shipping\ShipmentStatus;

it('finds the pickup points of a postcode nearest first and one by type and number', function () {
    $points = new FakePickupPoints;
    $points->seed(new PickupPoint('postoffice', '662', 'Postfiliale 662', 'Büchel 53', '52062', 'Aachen', 'DE', 300));
    $points->seed(new PickupPoint('locker', '130', 'Packstation 130', 'Markt 45', '52062', 'Aachen', 'DE', 177));
    $points->seed(new PickupPoint('locker', '201', 'Packstation 201', 'Am Hof 1', '53113', 'Bonn', 'DE', 50));

    expect(array_map(fn (PickupPoint $point): string => $point->label, $points->near('de', '52062', 'Aachen')))->toBe(['Packstation 130', 'Postfiliale 662'])
        ->and($points->near('DE', '99999'))->toBe([])
        ->and($points->searches()[0])->toBe(['country' => 'DE', 'postal_code' => '52062', 'city' => 'Aachen', 'street' => null])
        ->and($points->find('DE', '52062', 'locker', '130')?->toArray())->toBe(['type' => 'locker', 'id' => '130', 'label' => 'Packstation 130', 'street' => 'Markt 45', 'postal_code' => '52062', 'city' => 'Aachen', 'country' => 'DE', 'distance_meters' => 177])
        ->and($points->find('DE', '52062', 'postoffice', '130'))->toBeNull()
        ->and($points->find('DE', '53113', 'locker', '130'))->toBeNull()
        ->and($points->countries())->toBe(['DE']);

    $points->failNext('DHL ist nicht erreichbar.');
    expect(fn () => $points->near('DE', '52062'))->toThrow(ShipmentException::class, 'DHL ist nicht erreichbar.')
        ->and($points->near('DE', '52062'))->toHaveCount(2);
});

it('knows what a post number looks like', function (string $number, bool $valid) {
    expect((new FakePickupPoints)->validPostNumber($number))->toBe($valid);
})->with([['123456', true], ['1234567890', true], ['12345', false], ['12345678901', false], ['12 34 56', false], ['', false], ['abcdef', false]]);

it('carries a pickup point in the address and what the carrier says about a parcel', function () {
    $shipments = new FakeShipments;
    $actor = new ActorRef(1, 'Lars');
    $carrier = $shipments->addCarrier('DHL', true);
    $house = new ShipmentAddress('Erika Muster', 'Pontstraße 1', '52062', 'Aachen', 'DE');
    $locker = new ShipmentAddress('Erika Muster', 'Markt 45', '52062', 'Aachen', 'DE', pickupType: 'locker', pickupId: '130', postNumber: '1234567');
    $shipment = $shipments->create(new NewShipment(1, $carrier->id, $locker, [new NewParcel(1000)]), $actor);

    expect($house->isPickupPoint())->toBeFalse()
        ->and($locker->isPickupPoint())->toBeTrue()
        ->and($shipment->recipient->toArray())->toMatchArray(['pickup_type' => 'locker', 'pickup_id' => '130', 'post_number' => '1234567'])
        ->and($shipment->parcels[0]->trackingStatus)->toBeNull();

    // Nothing known yet: asking changes nothing.
    expect($shipments->track($shipment->id)->status)->toBe(ShipmentStatus::Created);

    $shipments->markShipped($shipment->id, $actor);
    $shipments->carrierSays($shipment->id, 'transit', 'Die Sendung wurde in das Zustellfahrzeug geladen.');
    $onItsWay = $shipments->track($shipment->id);

    expect($onItsWay->status)->toBe(ShipmentStatus::Shipped)
        ->and($onItsWay->parcels[0]->toArray())->toMatchArray(['tracking_status' => 'transit', 'tracking_text' => 'Die Sendung wurde in das Zustellfahrzeug geladen.', 'tracking_at' => '2030-01-04T09:30:00+00:00']);

    $shipments->carrierSays($shipment->id, 'delivered', 'Die Sendung wurde zugestellt.', '2030-01-05T11:00:00+00:00');
    $delivered = $shipments->track($shipment->id);

    expect($delivered->status)->toBe(ShipmentStatus::Delivered)
        ->and($delivered->deliveredOn)->toBe('2030-01-05')
        ->and($shipments->tracked())->toBe([$shipment->id, $shipment->id, $shipment->id])
        ->and(fn () => $shipments->track(99))->toThrow(ShipmentException::class);
});
