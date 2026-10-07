<?php

declare(strict_types=1);

use MyTechIO\Contracts\Fakes\FakeModuleState;

it('reports every module as active and enabled by default', function () {
    $state = new FakeModuleState;

    expect($state->isActive('domainrobot'))->toBeTrue()
        ->and($state->isEnabled('domainrobot'))->toBeTrue();
});

it('deactivates a single module without affecting others', function () {
    $state = new FakeModuleState;

    $state->deactivate('domainrobot');

    expect($state->isActive('domainrobot'))->toBeFalse()
        ->and($state->isEnabled('domainrobot'))->toBeFalse()
        ->and($state->isActive('nextcloud'))->toBeTrue()
        ->and($state->isEnabled('nextcloud'))->toBeTrue();
});

it('reactivates a module via activate()', function () {
    $state = new FakeModuleState;
    $state->deactivate('domainrobot');

    $state->activate('domainrobot');

    expect($state->isActive('domainrobot'))->toBeTrue()
        ->and($state->isEnabled('domainrobot'))->toBeTrue();
});

it('returns itself from activate() and deactivate() for fluent seeding', function () {
    $state = new FakeModuleState;

    expect($state->deactivate('domainrobot'))->toBe($state)
        ->and($state->activate('domainrobot'))->toBe($state);
});
