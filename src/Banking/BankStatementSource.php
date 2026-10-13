<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Banking;

/**
 * Origin of bank statements, container tag
 * `mytechio.bank_statement_sources`. Implement `FileStatementSource` for
 * uploaded files or `FetchStatementSource` for bank connections. The core
 * stages whatever a source returns, shows a preview and books nothing until
 * a person confirms it.
 */
interface BankStatementSource
{
    public const string KIND_FILE = 'file';

    public const string KIND_FETCH = 'fetch';

    /**
     * Unique key of the source == module name, e.g. `"bank-import"`.
     */
    public function sourceKey(): string;

    /**
     * Display name for the account dialog and the import preview, e.g.
     * `"Dateiimport (camt.053, MT940, CSV)"`.
     */
    public function label(): string;

    /**
     * `"file"` for sources that parse an uploaded payload, `"fetch"` for
     * sources that pull statements from a bank connection.
     */
    public function kind(): string;
}
