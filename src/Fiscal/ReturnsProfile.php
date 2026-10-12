<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The tax returns of the profile (VAT return, recapitulative statement, ...).
 * The first contract version only names them; the field mapping follows in a
 * later version.
 */
interface ReturnsProfile
{
    /**
     * Identifiers of the returns the profile can produce, e.g. `ustva`, `zm`, `oss`.
     *
     * @return list<string>
     */
    public function kinds(): array;
}
