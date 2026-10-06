<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Optionen für `IncomingDocuments::ingest()`.
 *
 * `metadata` geht 1:1 an die Kern-Ablage durch — ein Flag wie das frühere
 * `skip_notify` ist hier nicht mehr vorgesehen: Ob ein Modul seinen eigenen
 * Push (z. B. nach Paperless) für einen gerade selbst gezogenen Beleg
 * überspringt, entscheidet das Modul anhand seiner eigenen Daten, nicht
 * über diesen Vertrag.
 */
final readonly class IngestOptions
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public ?int $actorId = null,
        public array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'actor_id' => $this->actorId,
            'metadata' => $this->metadata,
        ];
    }
}
