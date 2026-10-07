<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Who initiated a booking — `id` is the user ID in the core (`null`
 * for automated/job-triggered bookings), `label` is a name readable
 * in the audit log.
 */
final readonly class ActorRef
{
    public function __construct(
        public ?int $id,
        public string $label,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
        ];
    }
}
