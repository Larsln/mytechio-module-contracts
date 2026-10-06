<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

/**
 * Ergebnis einer VIES-Prüfung einer USt-ID.
 *
 * `name`/`address` sind `null`, wenn der Dienst zur gültigen USt-ID keine
 * Stammdaten liefert (z. B. bei einfacher statt qualifizierter Bestätigung).
 * `requestIdentifier` ist die vom Dienst vergebene Vorgangsnummer (Nachweis
 * für die Dokumentationspflicht), `checkedAt` ein ISO-8601-Zeitstempel.
 */
final readonly class VatIdCheckResult
{
    public function __construct(
        public bool $valid,
        public ?string $name,
        public ?string $address,
        public string $requestIdentifier,
        public string $checkedAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'name' => $this->name,
            'address' => $this->address,
            'request_identifier' => $this->requestIdentifier,
            'checked_at' => $this->checkedAt,
        ];
    }
}
