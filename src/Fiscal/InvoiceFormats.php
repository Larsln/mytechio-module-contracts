<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The electronic invoice formats of the profile (mapping, mandatory fields,
 * rounding). The first contract version only names them; the details follow in
 * a later version.
 */
interface InvoiceFormats
{
    /**
     * Identifiers of the formats, e.g. `zugferd`, `xrechnung`.
     *
     * @return list<string>
     */
    public function formats(): array;
}
