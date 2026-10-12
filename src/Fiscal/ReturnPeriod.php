<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * One reporting period of a return: number, label, first and last day, filing
 * deadline (ISO dates) and whether the special advance payment is credited in it.
 */
final readonly class ReturnPeriod
{
    public function __construct(
        public int $no,
        public string $label,
        public string $startsOn,
        public string $endsOn,
        public string $dueOn,
        public bool $appliesSpecialPrepayment = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'no' => $this->no,
            'label' => $this->label,
            'starts_on' => $this->startsOn,
            'ends_on' => $this->endsOn,
            'due_on' => $this->dueOn,
            'applies_special_prepayment' => $this->appliesSpecialPrepayment,
        ];
    }
}
