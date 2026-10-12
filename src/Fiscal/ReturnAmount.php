<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * An amount of a return box: the sum of its terms over the period, read on the
 * given journal side.
 */
final readonly class ReturnAmount
{
    /**
     * @param  list<ReturnTerm>  $terms
     */
    public function __construct(
        public array $terms,
        public ReturnSide $side = ReturnSide::Credit,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'terms' => array_map(fn (ReturnTerm $term): array => $term->toArray(), $this->terms),
            'side' => $this->side->value,
        ];
    }
}
