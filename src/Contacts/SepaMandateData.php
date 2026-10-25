<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

/**
 * Read-only view of a SEPA mandate.
 *
 * `status` is `active`, `revoked` or `expired`; `type` is `core` or `b2b`.
 * The full IBAN is never exposed: `ibanMasked` shows the first and last four
 * characters in groups of four (`DE89 **** **** **** **30 00`), or only
 * `**** 3000` when just the last four digits are known.
 */
final readonly class SepaMandateData
{
    public function __construct(
        public int $id,
        public int $contactId,
        public string $reference,
        public string $type,
        public string $status,
        public string $signedAt,
        public ?string $revokedAt,
        public ?string $revocationReason,
        public ?string $ibanMasked,
        public ?string $source,
        public ?string $externalId,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'contact_id' => $this->contactId,
            'reference' => $this->reference,
            'type' => $this->type,
            'status' => $this->status,
            'signed_at' => $this->signedAt,
            'revoked_at' => $this->revokedAt,
            'revocation_reason' => $this->revocationReason,
            'iban_masked' => $this->ibanMasked,
            'source' => $this->source,
            'external_id' => $this->externalId,
        ];
    }
}
