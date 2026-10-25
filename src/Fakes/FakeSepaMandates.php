<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Contacts\NewSepaMandate;
use MyTechIO\Contracts\Contacts\SepaMandateData;
use MyTechIO\Contracts\Contacts\SepaMandates;
use MyTechIO\Contracts\ContractException;

/**
 * Test double for `SepaMandates`: in-memory store with ascending ids.
 * `create()` records the mandate as `active` and rejects a taken reference or
 * a repeated (source, externalId) pair; `revoke()` flips the status to
 * `revoked` once and leaves an already revoked mandate untouched. The
 * fake does not know contacts, so `create()` never fails on a missing one.
 */
final class FakeSepaMandates implements SepaMandates
{
    /**
     * @var array<int, SepaMandateData>
     */
    private array $mandates = [];

    /**
     * @var list<NewSepaMandate>
     */
    private array $created = [];

    /**
     * @var list<array{id: int, reason: string}>
     */
    private array $revoked = [];

    private int $nextId = 1;

    public function find(int $id): ?SepaMandateData
    {
        return $this->mandates[$id] ?? null;
    }

    public function forContact(int $contactId): array
    {
        $matches = array_values(array_filter(
            $this->mandates,
            static fn (SepaMandateData $mandate): bool => $mandate->contactId === $contactId,
        ));

        return array_reverse($matches);
    }

    public function active(int $contactId): ?SepaMandateData
    {
        foreach ($this->forContact($contactId) as $mandate) {
            if ($mandate->status === 'active') {
                return $mandate;
            }
        }

        return null;
    }

    public function create(NewSepaMandate $mandate): SepaMandateData
    {
        foreach ($this->mandates as $existing) {
            if ($existing->reference === $mandate->reference) {
                throw new ContractException("SEPA mandate reference {$mandate->reference} is already taken.");
            }

            if ($mandate->source !== null && $mandate->externalId !== null
                && $existing->source === $mandate->source && $existing->externalId === $mandate->externalId) {
                throw new ContractException("SEPA mandate {$mandate->source}/{$mandate->externalId} already exists.");
            }
        }

        $id = $this->nextId++;
        $this->created[] = $mandate;

        return $this->mandates[$id] = new SepaMandateData(
            id: $id,
            contactId: $mandate->contactId,
            reference: $mandate->reference,
            type: $mandate->type,
            status: 'active',
            signedAt: $mandate->signedAt,
            revokedAt: null,
            revocationReason: null,
            ibanMasked: $this->mask($mandate),
            source: $mandate->source,
            externalId: $mandate->externalId,
        );
    }

    public function revoke(int $id, string $reason): SepaMandateData
    {
        $mandate = $this->mandates[$id] ?? throw new ContractException("SEPA mandate {$id} is unknown.");

        if ($mandate->status === 'revoked') {
            return $mandate;
        }

        $this->revoked[] = ['id' => $id, 'reason' => $reason];

        return $this->mandates[$id] = new SepaMandateData(
            id: $mandate->id,
            contactId: $mandate->contactId,
            reference: $mandate->reference,
            type: $mandate->type,
            status: 'revoked',
            signedAt: $mandate->signedAt,
            revokedAt: date('Y-m-d'),
            revocationReason: $reason,
            ibanMasked: $mandate->ibanMasked,
            source: $mandate->source,
            externalId: $mandate->externalId,
        );
    }

    /**
     * @return list<NewSepaMandate>
     */
    public function created(): array
    {
        return $this->created;
    }

    /**
     * @return list<array{id: int, reason: string}>
     */
    public function revocations(): array
    {
        return $this->revoked;
    }

    private function mask(NewSepaMandate $mandate): ?string
    {
        if ($mandate->iban !== null) {
            $iban = preg_replace('/\s+/', '', $mandate->iban) ?? '';
            $masked = substr($iban, 0, 4).str_repeat('*', max(0, strlen($iban) - 8)).substr($iban, -4);

            return implode(' ', str_split($masked, 4));
        }

        return $mandate->ibanLast4 !== null ? '**** '.$mandate->ibanLast4 : null;
    }
}
