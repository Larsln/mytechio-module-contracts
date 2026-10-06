<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Connectors;

/**
 * Eine Aktion, die ein Connector für ein Objekt anbietet (z. B.
 * Autorenew an/aus, Sperren/Entsperren) — der künftige Objekt-Picker
 * (Zielbild Phase 5) rendert daraus Buttons, geschützt durch `permission`.
 */
final readonly class ConnectorAction
{
    public function __construct(
        public string $key,
        public string $label,
        public ?string $permission = null,
        public bool $destructive = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'permission' => $this->permission,
            'destructive' => $this->destructive,
        ];
    }
}
