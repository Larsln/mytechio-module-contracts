<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Stammdaten eines Kontos aus dem Kontenplan.
 */
final readonly class AccountInfo
{
    public function __construct(
        public string $number,
        public string $name,
        public string $type,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}
