<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * What the profile's rule table decides for one line: the automatic scenario,
 * the scenarios a manual deviation may choose from (the automatic one is
 * always among them), a one-line explanation with the legal reference and the
 * two editor flags. The core adds the manual deviation rules on top.
 *
 * `classificationMissing` flags a free-text line taxed like a supply of goods;
 * `businessProofMissingChangesResult` flags a business customer without a
 * recorded proof whose line would be taxed differently with one.
 */
final readonly class ScenarioDecision
{
    /**
     * @param  list<string>  $allowed  scenario codes
     */
    public function __construct(
        public string $automatic,
        public array $allowed,
        public string $explanation,
        public bool $classificationMissing,
        public bool $businessProofMissingChangesResult,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'automatic' => $this->automatic,
            'allowed' => $this->allowed,
            'explanation' => $this->explanation,
            'classification_missing' => $this->classificationMissing,
            'business_proof_missing_changes_result' => $this->businessProofMissingChangesResult,
        ];
    }
}
