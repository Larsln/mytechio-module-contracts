<?php

declare(strict_types=1);

use MyTechIO\Contracts\Contacts\ContactMarker;
use MyTechIO\Contracts\Fakes\FakeContactMarker;

it('marks contacts with a detail and answers the list lookups', function () {
    $marker = new FakeContactMarker('shop', 'Shop-Konto', 'S');
    $marker->mark(7, 'Aktiv');
    $marker->mark(9);

    expect($marker)->toBeInstanceOf(ContactMarker::class)
        ->and(ContactMarker::TAG)->toBe('mytechio.contact_markers')
        ->and($marker->key())->toBe('shop')
        ->and($marker->label())->toBe('Shop-Konto')
        ->and($marker->symbol())->toBe('S')
        ->and($marker->detailsFor([7, 8, 9]))->toBe([7 => 'Aktiv', 9 => null])
        ->and($marker->contactIds())->toBe([7, 9]);

    $marker->unmark(7);

    expect($marker->contactIds())->toBe([9])
        ->and($marker->detailsFor([7]))->toBe([]);
});
