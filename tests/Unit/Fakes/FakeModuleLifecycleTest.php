<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeModuleLifecycle;

it('counts calls per hook', function () {
    $lifecycle = new FakeModuleLifecycle;

    $lifecycle->onInstall();
    $lifecycle->onEnable();
    $lifecycle->onEnable();
    $lifecycle->onDisable();
    $lifecycle->onUninstall();

    expect($lifecycle->installCalls)->toBe(1)
        ->and($lifecycle->enableCalls)->toBe(2)
        ->and($lifecycle->disableCalls)->toBe(1)
        ->and($lifecycle->uninstallCalls)->toBe(1);
});

it('throws from onEnable after failOnEnable() but still counts the call', function () {
    $lifecycle = new FakeModuleLifecycle;
    $lifecycle->failOnEnable();

    expect(fn () => $lifecycle->onEnable())->toThrow(ContractException::class)
        ->and($lifecycle->enableCalls)->toBe(1);
});
