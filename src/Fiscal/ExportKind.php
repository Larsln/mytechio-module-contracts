<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A hand-over export of the profile (accountant format).
 */
final readonly class ExportKind
{
    public function __construct(
        public string $code,
        public string $name,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['code' => $this->code, 'name' => $this->name];
    }
}
