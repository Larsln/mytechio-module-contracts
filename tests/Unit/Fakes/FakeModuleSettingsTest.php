<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeModuleSettings;

it('returns the default when a key is unset', function () {
    $settings = new FakeModuleSettings;

    expect($settings->get('domainrobot', 'url', 'https://default.example'))->toBe('https://default.example');
});

it('stores, forgets and reports whether a key came from the environment', function () {
    $settings = new FakeModuleSettings;

    $settings->set('domainrobot', 'url', 'https://robot.s-dns.de');
    expect($settings->get('domainrobot', 'url'))->toBe('https://robot.s-dns.de')
        ->and($settings->isFromEnvironment('domainrobot', 'url'))->toBeFalse();

    $settings->markFromEnvironment('domainrobot', 'login');
    expect($settings->isFromEnvironment('domainrobot', 'login'))->toBeTrue();

    $settings->forget('domainrobot', 'url');
    expect($settings->get('domainrobot', 'url'))->toBeNull();
});

it('keeps values of different modules separate', function () {
    $settings = new FakeModuleSettings;

    $settings->set('domainrobot', 'key', 'a');
    $settings->set('other-module', 'key', 'b');

    expect($settings->get('domainrobot', 'key'))->toBe('a')
        ->and($settings->get('other-module', 'key'))->toBe('b');
});
