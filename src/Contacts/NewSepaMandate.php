<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Contacts;

/**
 * A SEPA mandate a module wants to register on a contact.
 *
 * `type` is `'core'` or `'b2b'`, `signedAt` an ISO date (`Y-m-d`). `source`
 * is the module key (e.g. `stripe`) and `externalId` the provider's id of
 * the mandate. Provide the full `iban` when known; a provider that only
 * reveals the end of the account number passes `ibanLast4` instead.
 */
final readonly class NewSepaMandate
{
    public function __construct(
        public int $contactId,
        public string $reference,
        public string $signedAt,
        public string $type = 'core',
        public ?string $source = null,
        public ?string $externalId = null,
        public ?string $iban = null,
        public ?string $ibanLast4 = null,
        public ?string $bankName = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'contact_id' => $this->contactId,
            'reference' => $this->reference,
            'signed_at' => $this->signedAt,
            'type' => $this->type,
            'source' => $this->source,
            'external_id' => $this->externalId,
            'iban' => $this->iban,
            'iban_last4' => $this->ibanLast4,
            'bank_name' => $this->bankName,
        ];
    }
}
