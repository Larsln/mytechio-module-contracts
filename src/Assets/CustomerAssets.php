<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

/**
 * Lesender und schreibender Zugriff auf Kundenobjekte (Domains, Lizenzen,
 * Hosting-Verträge, Zertifikate, …) aus Modulen heraus.
 *
 * Kern-Semantik: `create()`/`cancel()` feuern die Kern-Ereignisse
 * `CustomerAssetCreated`/`CustomerAssetCancelled`. `findByLabel()` ist die
 * bevorzugte Suche für Module, die ein Objekt anhand seines fachlichen
 * Namens (z. B. eines Domainnamens) wiederfinden wollen — aktive Objekte
 * werden zuerst zurückgegeben, danach nach `id` sortiert.
 */
interface CustomerAssets
{
    public function find(int $id): ?CustomerAssetData;

    /**
     * @return list<CustomerAssetData>
     */
    public function forContact(int $contactId, ?string $type = null): array;

    /**
     * Aktive zuerst, danach nach id.
     *
     * @return list<CustomerAssetData>
     */
    public function findByLabel(string $type, string $label): array;

    public function create(NewCustomerAsset $asset): CustomerAssetData;

    /**
     * @throws AssetNotFoundException wenn `$id` unbekannt ist.
     */
    public function cancel(int $id, ?string $cancelledAt = null): CustomerAssetData;
}
