<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Banking;

/**
 * A source that parses an uploaded statement file (camt.053, MT940, CSV, ...).
 * `kind()` returns `"file"`.
 */
interface FileStatementSource extends BankStatementSource
{
    /**
     * File extensions (lower case, without dot, e.g. `"xml"`) and MIME types
     * (e.g. `"text/csv"`) the source accepts. The upload dialog uses them as
     * a file-picker hint; the upload is validated against the union of all
     * sources.
     *
     * @return list<string>
     */
    public function accepts(): array;

    /**
     * Confidence between 0 and 100 that the payload belongs to this source.
     * 0 means "not mine". The core picks the source with the highest value
     * when the person does not choose one. Must not throw.
     */
    public function detect(string $filename, string $payload): int;

    /**
     * @throws StatementParseException when the payload cannot be read; the
     *                                 message is shown to the person.
     */
    public function parse(string $payload, StatementContext $context): ParsedStatementSet;
}
