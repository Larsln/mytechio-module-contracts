<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeWebsiteClients;
use MyTechIO\Contracts\Web\WebsiteAuthStatus;
use MyTechIO\Contracts\Web\WebsiteClients;

it('accepts a seeded key from any address when it has no allowlist', function () {
    $clients = new FakeWebsiteClients;
    $clients->seed('mtw_secret', 3, 'Webseite');

    $result = $clients->authenticate('mtw_secret', '203.0.113.9');

    expect($clients)->toBeInstanceOf(WebsiteClients::class)
        ->and($clients->available())->toBeTrue()
        ->and($result->isOk())->toBeTrue()
        ->and($result->clientId)->toBe(3)
        ->and($result->clientName)->toBe('Webseite')
        ->and($clients->authenticate('mtw_secret', null)->isOk())->toBeTrue();
});

it('refuses unknown and revoked keys', function () {
    $clients = new FakeWebsiteClients;
    $clients->seed('mtw_secret', 3, 'Webseite');
    $clients->revoke('mtw_secret');

    expect($clients->authenticate('mtw_other', '203.0.113.9')->status)->toBe(WebsiteAuthStatus::UnknownKey)
        ->and($clients->authenticate('mtw_secret', '203.0.113.9')->status)->toBe(WebsiteAuthStatus::UnknownKey);
});

it('applies exact IPs and CIDR ranges of the allowlist for IPv4 and IPv6', function () {
    $clients = new FakeWebsiteClients;
    $clients->seed('mtw_secret', 3, 'Webseite', ['203.0.113.7', '10.1.0.0/16', '2001:db8::/32']);

    expect($clients->authenticate('mtw_secret', '203.0.113.7')->isOk())->toBeTrue()
        ->and($clients->authenticate('mtw_secret', '10.1.44.2')->isOk())->toBeTrue()
        ->and($clients->authenticate('mtw_secret', '2001:db8:1::5')->isOk())->toBeTrue()
        ->and($clients->authenticate('mtw_secret', '10.2.0.1')->status)->toBe(WebsiteAuthStatus::IpDenied)
        ->and($clients->authenticate('mtw_secret', '2001:db9::1')->status)->toBe(WebsiteAuthStatus::IpDenied)
        ->and($clients->authenticate('mtw_secret', null)->status)->toBe(WebsiteAuthStatus::IpDenied)
        ->and($clients->authenticate('mtw_secret', '10.2.0.1')->clientId)->toBe(3);
});

it('answers unavailable while no holder is active', function () {
    $clients = new FakeWebsiteClients(available: false);
    $clients->seed('mtw_secret', 3, 'Webseite');

    expect($clients->available())->toBeFalse()
        ->and($clients->authenticate('mtw_secret', '203.0.113.7')->status)->toBe(WebsiteAuthStatus::Unavailable);

    $clients->setAvailable(true);

    expect($clients->authenticate('mtw_secret', '203.0.113.7')->isOk())->toBeTrue();
});

it('records every check without the secret', function () {
    $clients = new FakeWebsiteClients;
    $clients->seed('mtw_secret', 3, 'Webseite');
    $clients->authenticate('mtw_secret', '203.0.113.7');
    $clients->authenticate('mtw_wrong', '203.0.113.7');

    expect($clients->calls())->toBe([
        ['client_id' => 3, 'ip' => '203.0.113.7', 'status' => 'ok'],
        ['client_id' => null, 'ip' => '203.0.113.7', 'status' => 'unknown_key'],
    ]);
});
