<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Banking;

use MyTechIO\Contracts\Modules\HealthStatus;

/**
 * A source that pulls statements from a bank connection (FinTS, PSD2
 * aggregator, bank API). `kind()` returns `"fetch"`.
 */
interface FetchStatementSource extends BankStatementSource
{
    /**
     * @param  string  $from  first day to fetch, `YYYY-MM-DD`
     * @param  string  $to  last day to fetch, `YYYY-MM-DD`
     *
     * @throws StatementParseException when the bank connection fails or the
     *                                 response cannot be read.
     */
    public function fetch(string $accountIban, string $from, string $to, StatementContext $context): ParsedStatementSet;

    /**
     * State of the connection to the bank, e.g. warning when the consent has
     * expired. Reads cached or stored state only — no network call, the core
     * may ask on every page load.
     */
    public function connectionStatus(string $accountIban): HealthStatus;
}
