<?php

declare(strict_types=1);

use MyTechIO\Contracts\Web\WebsiteAuthResult;
use MyTechIO\Contracts\Web\WebsiteAuthStatus;

it('describes an accepted key with its client', function () {
    $result = WebsiteAuthResult::ok(7, 'Webseite');

    expect($result->isOk())->toBeTrue()
        ->and($result->status)->toBe(WebsiteAuthStatus::Ok)
        ->and($result->toArray())->toBe(['status' => 'ok', 'client_id' => 7, 'client_name' => 'Webseite']);
});

it('describes the refusals without a client where none is known', function () {
    expect(WebsiteAuthResult::unknownKey()->toArray())->toBe(['status' => 'unknown_key', 'client_id' => null, 'client_name' => null])
        ->and(WebsiteAuthResult::unavailable()->toArray())->toBe(['status' => 'unavailable', 'client_id' => null, 'client_name' => null])
        ->and(WebsiteAuthResult::ipDenied(7, 'Webseite')->toArray())->toBe(['status' => 'ip_denied', 'client_id' => 7, 'client_name' => 'Webseite'])
        ->and(WebsiteAuthResult::ipDenied(7, 'Webseite')->isOk())->toBeFalse();
});

it('names every status', function () {
    expect(array_map(fn (WebsiteAuthStatus $status) => $status->value, WebsiteAuthStatus::cases()))
        ->toBe(['ok', 'unknown_key', 'ip_denied', 'unavailable']);
});
