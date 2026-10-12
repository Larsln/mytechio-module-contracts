<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * Input of `NumberRanges::update()`: a scheduled format version that takes
 * effect on 1 January of `validFromYear` (the current series stays
 * untouched, so the year lies in the future, at most five years ahead).
 * `prefix` is up to 10 characters, `padding` 1 to 10 digits.
 */
final readonly class NumberRangeSettings
{
    public function __construct(
        public int $validFromYear,
        public string $prefix,
        public int $padding,
        public bool $includeYear,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'valid_from_year' => $this->validFromYear,
            'prefix' => $this->prefix,
            'padding' => $this->padding,
            'include_year' => $this->includeYear,
        ];
    }
}
