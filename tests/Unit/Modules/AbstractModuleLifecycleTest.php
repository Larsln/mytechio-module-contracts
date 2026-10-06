<?php

declare(strict_types=1);

use MyTechIO\Contracts\Modules\AbstractModuleLifecycle;
use MyTechIO\Contracts\Modules\ModuleLifecycle;

it('implements ModuleLifecycle with no-op defaults', function () {
    $lifecycle = new class extends AbstractModuleLifecycle {};

    expect($lifecycle)->toBeInstanceOf(ModuleLifecycle::class)
        ->and($lifecycle->onEnable())->toBeNull()
        ->and($lifecycle->onDisable())->toBeNull()
        ->and($lifecycle->onInstall())->toBeNull()
        ->and($lifecycle->onUninstall())->toBeNull();
});

it('allows overriding only the hooks a module actually needs', function () {
    $lifecycle = new class extends AbstractModuleLifecycle
    {
        public int $enableCalls = 0;

        public function onEnable(): void
        {
            $this->enableCalls++;
        }
    };

    $lifecycle->onEnable();
    $lifecycle->onDisable();

    expect($lifecycle->enableCalls)->toBe(1);
});
