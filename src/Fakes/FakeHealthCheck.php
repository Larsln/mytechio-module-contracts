<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Modules\HealthCheck;
use MyTechIO\Contracts\Modules\HealthStatus;

/**
 * Test double for `HealthCheck`: returns a predefined status,
 * `HealthStatus::ok()` by default. `withStatus()` sets the status for
 * the next call to `health()`.
 */
final class FakeHealthCheck implements HealthCheck
{
    private HealthStatus $status;

    public function __construct(?HealthStatus $status = null)
    {
        $this->status = $status ?? HealthStatus::ok('OK (Fake).');
    }

    public function health(): HealthStatus
    {
        return $this->status;
    }

    public function withStatus(HealthStatus $status): void
    {
        $this->status = $status;
    }
}
