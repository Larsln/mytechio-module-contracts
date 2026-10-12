<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The hand-over exports of the profile (accountant formats). The first contract
 * version only names them; the details follow in a later version.
 */
interface Exports
{
    /**
     * Identifiers of the exports, e.g. `datev`.
     *
     * @return list<string>
     */
    public function formats(): array;
}
