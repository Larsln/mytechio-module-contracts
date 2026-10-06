<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * KI-Extraktions-Provider für Eingangsrechnungen, Container-Tag
 * `mytechio.invoice_extractors`. Der Kern wählt über eine Konfiguration
 * genau einen Treiber aus (`extractorKey()`); mehrere installierte Module
 * stellen lediglich mehrere Treiber zur Auswahl, es läuft nie mehr als
 * einer je Extraktion.
 */
interface InvoiceExtractor
{
    /**
     * Eindeutiger Schlüssel des Treibers == Modulname, z. B.
     * `"extraction-anthropic"`.
     */
    public function extractorKey(): string;

    /**
     * Anzeigename für die Modulübersicht und den Posteingang-Hinweis, z. B.
     * `"Anthropic Claude"`.
     */
    public function label(): string;

    /**
     * Ob der Treiber einsatzbereit ist (z. B. API-Schlüssel hinterlegt).
     * Ein konfiguriertes Modul kann trotzdem deaktiviert sein — das prüft
     * der Kern separat über die Modul-Registrierung.
     */
    public function configured(): bool;

    /**
     * @return array<string, mixed> rohe Payload (Blätter supplier/invoice/items/totals plus meta.model) — Validierung macht der Kern.
     *
     * @throws ExtractionFailedException
     */
    public function extract(string $pdfBlob, ExtractionContextData $context): array;
}
