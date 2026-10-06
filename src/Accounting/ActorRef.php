<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Wer eine Buchung veranlasst hat — `id` ist die User-ID im Kern (`null`
 * bei automatischen/Job-ausgelösten Buchungen), `label` ein für das
 * Audit-Log lesbarer Name.
 */
final readonly class ActorRef
{
    public function __construct(
        public ?int $id,
        public string $label,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
        ];
    }
}
