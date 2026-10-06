<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Invoices;

/**
 * Positions-Erweiterung einer Rechnung durch ein Modul.
 *
 * Der Kern sammelt Implementierungen über den Container-Tag
 * `mytechio.invoice_item_extensions` (siehe `InvoiceItemExtensionRegistry`
 * im Kern) und speichert je Rechnungsposition ein JSON-Feld `extras`, in
 * dem jede Erweiterung ihren eigenen Teil unter dem Schlüssel `key()`
 * ablegt — `key()` MUSS dabei dem Modulnamen entsprechen, damit der Kern
 * Erweiterungen deaktivierter Module herausfiltern kann.
 */
interface InvoiceItemExtension
{
    /**
     * Schlüssel unter `extras` einer Position, z. B. `"customer_assets"`.
     * Entspricht dem Modulnamen.
     */
    public function key(): string;

    /**
     * @param  array<string, mixed>  $extras  Der eigene Teil (extras[key()]).
     * @return array<string, string> Feld ⇒ Fehlertext (leer = gültig)
     */
    public function validate(array $extras, ?int $contactId): array;

    /**
     * Nach dem Neuanlegen der Positionen (innerhalb der Kern-Transaktion).
     *
     * @param  list<array{id: int, extras: array<string, mixed>}>  $items  (extras = eigener Teil)
     */
    public function afterItemsSynced(int $invoiceId, ?int $contactId, array $items): void;

    /**
     * Zusatzzeilen unter der Positionsbeschreibung auf dem Beleg.
     *
     * @param  array<string, mixed>  $extras
     * @return list<string>
     */
    public function annotate(int $invoiceItemId, array $extras): array;

    /**
     * Eigener Teil der extras für eine Belegkopie (Duplizieren/Storno):
     * z. B. Verknüpfungen entfernen.
     *
     * @param  array<string, mixed>  $extras
     * @return array<string, mixed>
     */
    public function duplicate(array $extras): array;
}
