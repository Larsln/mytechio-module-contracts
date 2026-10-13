<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Banking;

/**
 * Everything a source delivers for one payload or fetch window: the
 * statements and the warnings the source wants the person to see in the
 * import preview (skipped lines, assumptions, truncated data).
 */
final readonly class ParsedStatementSet
{
    /**
     * @param  list<ParsedStatement>  $statements
     * @param  list<string>  $warnings  German texts shown in the preview
     */
    public function __construct(
        public array $statements,
        public array $warnings = [],
    ) {}

    /**
     * Number of booked lines over all statements (batch children not counted).
     */
    public function transactionCount(): int
    {
        return array_sum(array_map(
            static fn (ParsedStatement $statement): int => count($statement->transactions),
            $this->statements,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'statements' => array_map(
                static fn (ParsedStatement $statement): array => $statement->toArray(),
                $this->statements,
            ),
            'warnings' => $this->warnings,
        ];
    }

    /**
     * Inverse of `toArray()`.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws StatementParseException
     */
    public static function fromArray(array $data): self
    {
        $statements = is_array($data['statements'] ?? null) ? $data['statements'] : [];
        $warnings = is_array($data['warnings'] ?? null) ? $data['warnings'] : [];

        return new self(
            statements: array_values(array_map(
                static fn (array $statement): ParsedStatement => ParsedStatement::fromArray($statement),
                array_filter($statements, 'is_array'),
            )),
            warnings: array_values(array_filter($warnings, 'is_string')),
        );
    }
}
