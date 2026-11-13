<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Shipping\Carriers;

/**
 * What a carrier says about a parcel (since 2.20). `status` is one of
 * {@see self::STATUSES}, `text` the carrier's own words and `at` the
 * moment of that event (ISO 8601), both null when the carrier named none.
 */
final readonly class TrackingState
{
    public const string DELIVERED = 'delivered';

    public const array STATUSES = ['pre-transit', 'transit', self::DELIVERED, 'failure', 'unknown'];

    public string $status;

    public function __construct(
        string $status,
        public ?string $text = null,
        public ?string $at = null,
    ) {
        $this->status = in_array($status, self::STATUSES, true) ? $status : 'unknown';
    }

    public function isDelivered(): bool
    {
        return $this->status === self::DELIVERED;
    }
}
