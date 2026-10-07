<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

use MyTechIO\Contracts\Connectors\ConnectorStatus;

/**
 * Read and write access to customer assets (Kundenobjekte) (domains,
 * licenses, hosting contracts, certificates, …) from modules.
 *
 * Core semantics: `create()`/`cancel()` fire the core events
 * `CustomerAssetCreated`/`CustomerAssetCancelled`. `findByLabel()` is the
 * preferred lookup for modules that want to find an asset by its
 * business-domain name (e.g. a domain name) — active assets are returned
 * first, then sorted by `id`. `findBySource()` is the preferred lookup
 * for connector modules that want to find an asset by its external
 * identifier.
 */
interface CustomerAssets
{
    public function find(int $id): ?CustomerAssetData;

    /**
     * @return list<CustomerAssetData>
     */
    public function forContact(int $contactId, ?string $type = null): array;

    /**
     * Active first, then by id.
     *
     * @return list<CustomerAssetData>
     */
    public function findByLabel(string $type, string $label): array;

    public function findBySource(string $source, string $externalId): ?CustomerAssetData;

    public function create(NewCustomerAsset $asset): CustomerAssetData;

    /**
     * Sets the source + external identifier on a previously manual asset
     * (legacy data), e.g. when a connector subsequently assigns a source
     * to a domain that was previously created freely.
     *
     * @throws AssetNotFoundException if `$id` is unknown.
     */
    public function attachSource(int $id, string $source, string $externalId): CustomerAssetData;

    /**
     * Applies a connector's report (status, renewal, autorenew,
     * attributes) to the customer asset. `attributes` are merged with
     * the existing ones, not replaced.
     *
     * @throws AssetNotFoundException if `$id` is unknown.
     */
    public function updateFromSource(int $id, ConnectorStatus $status): CustomerAssetData;

    /**
     * @return list<CustomerAssetData> Assets attached to this invoice item.
     */
    public function forInvoiceItem(int $invoiceItemId): array;

    /**
     * @throws AssetNotFoundException if `$id` is unknown.
     */
    public function cancel(int $id, ?string $cancelledAt = null): CustomerAssetData;
}
