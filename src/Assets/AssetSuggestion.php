<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Assets;

/**
 * Ein Vorschlag eines `AssetSource`-Moduls für den künftigen
 * Objekt-Picker (Zielbild Phase 5).
 *
 * `externalId` ist der Schlüssel, den das Modul selbst verwendet (z. B.
 * ein Domainname) — nicht notwendigerweise die ID eines Kundenobjekts im
 * Kern, da das Objekt unter Umständen noch nicht angelegt ist.
 */
final readonly class AssetSuggestion
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public string $source,
        public string $type,
        public string $externalId,
        public string $label,
        public array $attributes = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'type' => $this->type,
            'external_id' => $this->externalId,
            'label' => $this->label,
            'attributes' => $this->attributes,
        ];
    }
}
