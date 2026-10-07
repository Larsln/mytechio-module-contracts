<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Connectors;

/**
 * An action that a connector offers for an asset (e.g. enable/disable
 * autorenew, lock/unlock) — the future asset picker (Phase 5 target
 * design) renders buttons from this, protected by `permission`.
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
