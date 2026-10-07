<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Connectors;

/**
 * Result of a full reconciliation (`Connector::sync()`).
 */
final readonly class SyncReport
{
    /**
     * @param  list<string>  $notes
     */
    public function __construct(
        public int $created,
        public int $updated,
        public int $removed,
        public array $notes = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'removed' => $this->removed,
            'notes' => $this->notes,
        ];
    }
}
