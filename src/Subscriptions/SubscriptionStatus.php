<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Subscriptions;

/**
 * Lifecycle status of a subscription (German UI: "Leistung").
 *
 * `Cancelled` means the notice has been received and the subscription runs
 * until its `endsOn` date; `Ended` means it no longer runs.
 */
enum SubscriptionStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Cancelled = 'cancelled';
    case Ended = 'ended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktiv',
            self::Paused => 'Pausiert',
            self::Cancelled => 'Gekündigt',
            self::Ended => 'Beendet',
        };
    }
}
