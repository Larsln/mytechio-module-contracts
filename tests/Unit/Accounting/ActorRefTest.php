<?php

declare(strict_types=1);

use MyTechIO\Contracts\Accounting\ActorRef;

it('allows a null id for automated bookings', function () {
    $actor = new ActorRef(id: null, label: 'Queue-Job');

    expect($actor->toArray())->toBe(['id' => null, 'label' => 'Queue-Job']);
});
