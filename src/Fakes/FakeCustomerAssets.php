<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Assets\AssetNotFoundException;
use MyTechIO\Contracts\Assets\CustomerAssetData;
use MyTechIO\Contracts\Assets\CustomerAssets;
use MyTechIO\Contracts\Assets\NewCustomerAsset;
use MyTechIO\Contracts\Connectors\ConnectorStatus;

/**
 * Test double for `CustomerAssets`: in-memory store with auto IDs.
 * `seed()` lets tests specify the initial state, including already
 * occupied IDs. `findByLabel()` sorts active objects
 * (`status === 'active'`) first, just like the core implementation.
 */
final class FakeCustomerAssets implements CustomerAssets
{
    /**
     * @var array<int, CustomerAssetData>
     */
    private array $assets = [];

    private int $nextId = 1;

    public function seed(CustomerAssetData $asset): void
    {
        $this->assets[$asset->id] = $asset;
        $this->nextId = max($this->nextId, $asset->id + 1);
    }

    public function find(int $id): ?CustomerAssetData
    {
        return $this->assets[$id] ?? null;
    }

    public function forContact(int $contactId, ?string $type = null): array
    {
        return array_values(array_filter(
            $this->assets,
            static fn (CustomerAssetData $asset): bool => $asset->contactId === $contactId
                && ($type === null || $asset->type === $type),
        ));
    }

    public function findByLabel(string $type, string $label): array
    {
        $matches = array_values(array_filter(
            $this->assets,
            static fn (CustomerAssetData $asset): bool => $asset->type === $type && $asset->label === $label,
        ));

        usort($matches, static function (CustomerAssetData $a, CustomerAssetData $b): int {
            $activeA = $a->status === 'active' ? 0 : 1;
            $activeB = $b->status === 'active' ? 0 : 1;

            return $activeA <=> $activeB ?: $a->id <=> $b->id;
        });

        return $matches;
    }

    public function findBySource(string $source, string $externalId): ?CustomerAssetData
    {
        foreach ($this->assets as $asset) {
            if ($asset->source === $source && $asset->externalId === $externalId) {
                return $asset;
            }
        }

        return null;
    }

    public function create(NewCustomerAsset $asset): CustomerAssetData
    {
        $id = $this->nextId++;

        $data = new CustomerAssetData(
            id: $id,
            contactId: $asset->contactId,
            type: $asset->type,
            label: $asset->label,
            status: 'active',
            acquiredAt: $asset->acquiredAt,
            cancelledAt: null,
            invoiceItemId: null,
            notes: $asset->notes,
            source: $asset->source,
            externalId: $asset->externalId,
            providerName: $asset->providerName,
            renewsAt: $asset->renewsAt,
            autorenew: $asset->autorenew,
            attributes: $asset->attributes,
        );

        $this->assets[$id] = $data;

        return $data;
    }

    public function attachSource(int $id, string $source, string $externalId): CustomerAssetData
    {
        $current = $this->assets[$id] ?? null;

        if ($current === null) {
            throw new AssetNotFoundException($id);
        }

        $updated = new CustomerAssetData(
            id: $current->id,
            contactId: $current->contactId,
            type: $current->type,
            label: $current->label,
            status: $current->status,
            acquiredAt: $current->acquiredAt,
            cancelledAt: $current->cancelledAt,
            invoiceItemId: $current->invoiceItemId,
            notes: $current->notes,
            source: $source,
            externalId: $externalId,
            providerName: $current->providerName,
            renewsAt: $current->renewsAt,
            autorenew: $current->autorenew,
            externalStatus: $current->externalStatus,
            attributes: $current->attributes,
        );

        $this->assets[$id] = $updated;

        return $updated;
    }

    public function updateFromSource(int $id, ConnectorStatus $status): CustomerAssetData
    {
        $current = $this->assets[$id] ?? null;

        if ($current === null) {
            throw new AssetNotFoundException($id);
        }

        $updated = new CustomerAssetData(
            id: $current->id,
            contactId: $current->contactId,
            type: $current->type,
            label: $current->label,
            status: $current->status,
            acquiredAt: $current->acquiredAt,
            cancelledAt: $current->cancelledAt,
            invoiceItemId: $current->invoiceItemId,
            notes: $current->notes,
            source: $current->source,
            externalId: $current->externalId,
            providerName: $current->providerName,
            renewsAt: $status->renewsAt,
            autorenew: $status->autorenew,
            externalStatus: $status->status,
            attributes: [...$current->attributes, ...$status->attributes],
        );

        $this->assets[$id] = $updated;

        return $updated;
    }

    /**
     * @return list<CustomerAssetData>
     */
    public function forInvoiceItem(int $invoiceItemId): array
    {
        return array_values(array_filter(
            $this->assets,
            static fn (CustomerAssetData $asset): bool => $asset->invoiceItemId === $invoiceItemId,
        ));
    }

    public function cancel(int $id, ?string $cancelledAt = null): CustomerAssetData
    {
        $current = $this->assets[$id] ?? null;

        if ($current === null) {
            throw new AssetNotFoundException($id);
        }

        $cancelled = new CustomerAssetData(
            id: $current->id,
            contactId: $current->contactId,
            type: $current->type,
            label: $current->label,
            status: 'cancelled',
            acquiredAt: $current->acquiredAt,
            cancelledAt: $cancelledAt ?? $current->cancelledAt,
            invoiceItemId: $current->invoiceItemId,
            notes: $current->notes,
            source: $current->source,
            externalId: $current->externalId,
            providerName: $current->providerName,
            renewsAt: $current->renewsAt,
            autorenew: $current->autorenew,
            externalStatus: $current->externalStatus,
            attributes: $current->attributes,
        );

        $this->assets[$id] = $cancelled;

        return $cancelled;
    }
}
