<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Accounting;

/**
 * One scheduled (future-dated) format version of a number range: it takes
 * effect on 1 January of `validFromYear`. `preview` is the first number the
 * format would produce.
 */
final readonly class NumberRangeVersion
{
    public function __construct(
        public int $validFromYear,
        public string $prefix,
        public int $padding,
        public bool $includeYear,
        public string $preview,
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
            'preview' => $this->preview,
        ];
    }
}
