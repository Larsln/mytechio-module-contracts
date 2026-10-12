<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Fiscal\Exports;

/**
 * Test double for `Exports` of `FakeFiscalProfile`: no export kinds, like the
 * German profile before its DATEV export exists.
 */
final class FakeExports implements Exports
{
    public function kinds(): array
    {
        return [];
    }
}
