<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The box number and label of a per-rate box for one rate key (19 % and 7 % have
 * their own boxes; every other rate falls back to the box's own texts).
 */
final readonly class ReturnRateVariant
{
    public function __construct(
        public string $rateKey,
        public string $kz,
        public string $label,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['rate_key' => $this->rateKey, 'kz' => $this->kz, 'label' => $this->label];
    }
}
