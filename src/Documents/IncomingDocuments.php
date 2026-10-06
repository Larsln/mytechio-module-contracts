<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Posteingang-Ingest für Module.
 *
 * Kern-Semantik: Die lokale GoBD-Ablage und der Posteingang gehören dem
 * Kern — Module liefern über diesen Vertrag lediglich den Rohinhalt eines
 * Belegs ein (z. B. aus einem Pull von Paperless-NGX). Erfolgreiches
 * `ingest()` legt den Beleg in der Kern-Ablage an und feuert das
 * Kern-Ereignis `DocumentStored`.
 */
interface IncomingDocuments
{
    /**
     * Legt einen Beleg im Posteingang an. Eine Hash-Dublette (gleicher
     * `sha256` des Blobs wie ein bereits lebender Beleg) legt keinen neuen
     * Beleg an, sondern liefert den bestehenden mit `duplicate = true`
     * zurück — so kann ein Modul einen Pull wiederholt ausführen, ohne
     * Dubletten zu erzeugen.
     *
     * @param  string  $blob  Rohinhalt der Datei (binär).
     * @param  string  $source  Freier Modulschlüssel der Quelle (z. B. `paperless`); `upload` ist dem Kern vorbehalten.
     */
    public function ingest(string $blob, string $filename, string $source, IngestOptions $options): IngestedDocument;

    /**
     * Liefert den Verweis auf einen Posteingang-Beleg anhand seiner ID, oder
     * `null`, wenn keiner mit dieser ID existiert.
     */
    public function find(int $id): ?IncomingDocumentRef;
}
