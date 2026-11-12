<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeCarrierDriver;
use MyTechIO\Contracts\Fakes\FakePickupPoints;
use MyTechIO\Contracts\Shipping\Carriers\CarrierDriver;
use MyTechIO\Contracts\Shipping\Carriers\CreatesReturnLabels;
use MyTechIO\Contracts\Shipping\Carriers\PickupPointProvider;
use MyTechIO\Contracts\Shipping\Carriers\TrackingState;
use MyTechIO\Contracts\Shipping\Carriers\TracksParcels;
use MyTechIO\Contracts\Shipping\NewParcel;
use MyTechIO\Contracts\Shipping\NewShipment;
use MyTechIO\Contracts\Shipping\ShipmentAddress;
use MyTechIO\Contracts\Shipping\ShipmentException;

function driverShipment(int $parcels = 1): NewShipment
{
    return new NewShipment(1, 1, new ShipmentAddress('Erika Muster', 'Pontstraße 1', '52062', 'Aachen', 'DE'), array_fill(0, $parcels, new NewParcel(1000)), 'AB-1');
}

it('books every parcel with a number and a label, and cancels by reference', function () {
    $driver = new FakeCarrierDriver('gls', 'GLS (Schnittstelle)');
    $parcels = $driver->createShipment(driverShipment(2));

    expect($driver)->toBeInstanceOf(CarrierDriver::class)->toBeInstanceOf(TracksParcels::class)->toBeInstanceOf(CreatesReturnLabels::class)
        ->and($driver->key())->toBe('gls')
        ->and($driver->label())->toBe('GLS (Schnittstelle)')
        ->and(array_map(fn ($parcel) => $parcel->trackingNumber, $parcels))->toBe(['GLS-1', 'GLS-2'])
        ->and($parcels[0]->reference)->toBe('GLS-1')
        ->and(base64_decode((string) $parcels[0]->labelBase64))->toStartWith('%PDF-')
        ->and($driver->booked())->toHaveCount(1);

    $driver->cancelShipment(['GLS-1', 'GLS-2']);
    expect($driver->cancelled())->toBe([['GLS-1', 'GLS-2']]);

    $driver->failNext('GLS lehnt ab.');
    expect(fn () => $driver->createShipment(driverShipment()))->toThrow(ShipmentException::class, 'GLS lehnt ab.')
        ->and($driver->booked())->toHaveCount(1);

    $driver->ready(false);
    expect($driver->createsLabels())->toBeFalse()->and($driver->returnsReady())->toBeFalse();
});

it('tracks its own carriers within the budget and says what was seeded', function () {
    $driver = new FakeCarrierDriver;
    $driver->says('A1', new TrackingState('transit', 'Im Paketzentrum.', '2030-01-03T18:00:00+00:00'));
    $driver->budget(2);

    expect($driver->tracksCarrier('fake', null))->toBeTrue()
        ->and($driver->tracksCarrier('manual', 'https://track.example.test/{number}'))->toBeFalse()
        ->and($driver->trackingState('A1')?->text)->toBe('Im Paketzentrum.')
        ->and($driver->trackingState('B2'))->toBeNull()
        ->and($driver->trackingBudget())->toBe(0)
        ->and($driver->asked())->toBe(['A1', 'B2'])
        ->and(fn () => $driver->trackingState('A1'))->toThrow(ShipmentException::class);

    $driver->budget(5);
    $driver->trackingOn(false);
    expect($driver->tracksCarrier('fake', null))->toBeFalse()->and($driver->trackingBudget())->toBe(0);
});

it('takes a status nobody knows as unknown', function () {
    expect((new TrackingState('exploded'))->status)->toBe('unknown')
        ->and((new TrackingState('delivered'))->isDelivered())->toBeTrue()
        ->and((new TrackingState('transit'))->isDelivered())->toBeFalse();
});

it('creates return labels and remembers who sends what back', function () {
    $driver = new FakeCarrierDriver;
    $sender = new ShipmentAddress('Erika Muster', 'Pontstraße 1', '52062', 'Aachen', 'DE');
    $label = $driver->createReturnLabel($sender, 'deu', 'AB-1', 1200);

    expect($label->number)->toBe('RETURN-1')
        ->and($label->content)->toStartWith('%PDF-')
        ->and($label->mime)->toBe('application/pdf')
        ->and($label->trackingUrl)->toBe('https://track.example.test/RETURN-1')
        ->and($driver->returns())->toBe([['sender' => $sender, 'receiver_id' => 'deu', 'reference' => 'AB-1', 'weight_grams' => 1200]]);

    $driver->ready(false);
    expect(fn () => $driver->createReturnLabel($sender, 'deu', null, null))->toThrow(ShipmentException::class);
});

it('lets the pickup fake stand in for a provider', function () {
    $provider = new FakePickupPoints;

    expect($provider)->toBeInstanceOf(PickupPointProvider::class)
        ->and($provider->carrier())->toBe('Testdienst')
        ->and(PickupPointProvider::TAG)->not->toBe(CarrierDriver::TAG);
});
