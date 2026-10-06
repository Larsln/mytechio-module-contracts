<?php

declare(strict_types=1);

use MyTechIO\Contracts\Contracts;

it('exposes the package version', function () {
    expect(Contracts::VERSION)->toBe('1.5.0');
});
