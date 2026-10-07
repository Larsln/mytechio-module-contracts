<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

/**
 * Result of a VIES check of a VAT ID.
 *
 * `name`/`address` are `null` if the service does not return master data
 * for the valid VAT ID (e.g. for a simple instead of a qualified
 * confirmation). `requestIdentifier` is the transaction number assigned
 * by the service (proof for the documentation obligation), `checkedAt` is
 * an ISO-8601 timestamp.
 */
final readonly class VatIdCheckResult
{
    public function __construct(
        public bool $valid,
        public ?string $name,
        public ?string $address,
        public string $requestIdentifier,
        public string $checkedAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'name' => $this->name,
            'address' => $this->address,
            'request_identifier' => $this->requestIdentifier,
            'checked_at' => $this->checkedAt,
        ];
    }
}
