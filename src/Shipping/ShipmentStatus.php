<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping;

/**
 * Where a shipment stands: `Created` (parcels and, with a carrier interface,
 * labels exist; nothing left the house), `Shipped`, `Delivered` and
 * `Cancelled` (never sent).
 */
enum ShipmentStatus: string
{
    case Created = 'created';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Vorbereitet',
            self::Shipped => 'Versendet',
            self::Delivered => 'Zugestellt',
            self::Cancelled => 'Storniert',
        };
    }

    /** Whether the goods left the house. */
    public function isOnItsWay(): bool
    {
        return $this === self::Shipped || $this === self::Delivered;
    }
}
