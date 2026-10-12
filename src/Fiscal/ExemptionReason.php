<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The reason an e-invoice states for an untaxed tax category: free text and/or
 * an exemption code (VATEX).
 */
final readonly class ExemptionReason
{
    public function __construct(
        public ?string $text,
        public ?string $code,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['text' => $this->text, 'code' => $this->code];
    }
}
