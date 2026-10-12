<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * An electronic invoice format of the profile and what the core can do with it.
 */
final readonly class DocumentFormatInfo
{
    public function __construct(
        public string $code,
        public string $name,
        public bool $canWrite,
        public bool $canRead,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['code' => $this->code, 'name' => $this->name, 'can_write' => $this->canWrite, 'can_read' => $this->canRead];
    }
}
