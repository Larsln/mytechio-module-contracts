<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The closing of the books: statement layouts and closing entries. The first
 * contract version only names the layouts; the details follow in a later version.
 */
interface ClosingProfile
{
    /**
     * Identifiers of the statements the profile lays out, e.g. `balance_sheet`, `profit_and_loss`.
     *
     * @return list<string>
     */
    public function statements(): array;
}
