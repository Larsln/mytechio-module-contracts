<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Modules\HealthCheck;
use MyTechIO\Contracts\Modules\HealthStatus;

/**
 * Test-Double für `HealthCheck`: liefert einen vorgegebenen Status,
 * standardmäßig `HealthStatus::ok()`. `withStatus()` setzt den Status für
 * den nächsten Aufruf von `health()`.
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
