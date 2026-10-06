<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

use MyTechIO\Contracts\Connectors\ConnectorStatus;

/**
 * Lesender und schreibender Zugriff auf Kundenobjekte (Domains, Lizenzen,
 * Hosting-Verträge, Zertifikate, …) aus Modulen heraus.
 *
 * Kern-Semantik: `create()`/`cancel()` feuern die Kern-Ereignisse
 * `CustomerAssetCreated`/`CustomerAssetCancelled`. `findByLabel()` ist die
 * bevorzugte Suche für Module, die ein Objekt anhand seines fachlichen
 * Namens (z. B. eines Domainnamens) wiederfinden wollen — aktive Objekte
 * werden zuerst zurückgegeben, danach nach `id` sortiert. `findBySource()`
 * ist die bevorzugte Suche für Connector-Module, die ein Objekt anhand
 * seiner externen Kennung wiederfinden wollen.
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

    public function findBySource(string $source, string $externalId): ?CustomerAssetData;

    public function create(NewCustomerAsset $asset): CustomerAssetData;

    /**
     * Setzt Quelle + externe Kennung an einem bisher manuellen Objekt
     * (Altbestand), z. B. wenn ein Connector eine zuvor frei angelegte
     * Domain nachträglich einer Quelle zuordnet.
     *
     * @throws AssetNotFoundException wenn `$id` unbekannt ist.
     */
    public function attachSource(int $id, string $source, string $externalId): CustomerAssetData;

    /**
     * Übernimmt die Rückmeldung eines Connectors (Status, Verlängerung,
     * Autorenew, Attribute) in das Kundenobjekt. `attributes` werden mit
     * den bestehenden zusammengeführt (merge), nicht ersetzt.
     *
     * @throws AssetNotFoundException wenn `$id` unbekannt ist.
     */
    public function updateFromSource(int $id, ConnectorStatus $status): CustomerAssetData;

    /**
     * @return list<CustomerAssetData> Objekte, die an dieser Rechnungsposition hängen.
     */
    public function forInvoiceItem(int $invoiceItemId): array;

    /**
     * @throws AssetNotFoundException wenn `$id` unbekannt ist.
     */
    public function cancel(int $id, ?string $cancelledAt = null): CustomerAssetData;
}
