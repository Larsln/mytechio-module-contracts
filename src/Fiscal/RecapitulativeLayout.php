<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * Layout of the recapitulative statement: the kinds of supply with their
 * scenario mapping and the country prefixes a counterparty's VAT ID may carry
 * (anything else is reported as invalid).
 */
final readonly class RecapitulativeLayout
{
    /**
     * @param  list<RecapitulativeKind>  $kinds
     * @param  list<string>  $countryPrefixes
     */
    public function __construct(
        public array $kinds,
        public array $countryPrefixes,
    ) {}

    /**
     * The kind a line of this outgoing scenario is reported under, or null when the scenario is not reported.
     */
    public function kindOfScenario(string $scenario): ?RecapitulativeKind
    {
        foreach ($this->kinds as $kind) {
            if (in_array($scenario, $kind->scenarios, true)) {
                return $kind;
            }
        }

        return null;
    }

    public function kind(string $code): ?RecapitulativeKind
    {
        foreach ($this->kinds as $kind) {
            if ($kind->code === $code) {
                return $kind;
            }
        }

        return null;
    }

    /**
     * Every outgoing scenario code reported on the statement.
     *
     * @return list<string>
     */
    public function scenarios(): array
    {
        return array_values(array_unique(array_merge(...array_map(fn (RecapitulativeKind $kind): array => $kind->scenarios, $this->kinds))));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'kinds' => array_map(fn (RecapitulativeKind $kind): array => $kind->toArray(), $this->kinds),
            'country_prefixes' => $this->countryPrefixes,
        ];
    }
}
