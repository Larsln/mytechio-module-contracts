<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

use MyTechIO\Contracts\Articles\TaxClassification;

/**
 * The line side of the place-of-supply rules: what the line supplies (null =
 * free text without a classification, taxed like a supply of goods) and the
 * service type of an other service.
 */
final readonly class LineTaxContext
{
    public function __construct(
        public ?TaxClassification $classification,
        public ?ServiceType $serviceType = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'classification' => $this->classification?->value,
            'service_type' => $this->serviceType?->value,
        ];
    }
}
