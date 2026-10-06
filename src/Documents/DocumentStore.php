<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Ablage beliebiger Dokumente (Belege, PDFs, Importe) für Module.
 *
 * Kern-Semantik: Die lokale GoBD-Ablage ist der Besitzer des Dokuments,
 * Paperless-NGX ist ein asynchroner Zweit-Viewer — ein Fehlschlag bei
 * Paperless darf `store()` nicht scheitern lassen. Nach erfolgreicher
 * Ablage feuert die Implementierung das Kern-Ereignis `DocumentStored`.
 * Module erhalten über diesen Vertrag keinen direkten Zugriff auf
 * Paperless oder das Dateisystem.
 */
interface DocumentStore
{
    /**
     * @param  string  $blob  Rohinhalt der Datei (binär).
     * @param  string  $directory  Logisches Zielverzeichnis, relativ zur Ablage-Konvention des Kerns (z. B. `documents/invoices/2026/10`).
     * @param  string  $filename  Dateiname inklusive Endung.
     */
    public function store(string $blob, string $directory, string $filename, DocumentMeta $meta): StoredDocument;
}
