<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The tax returns of the profile: which kinds it produces, the tax codes the
 * booking services stamp on journal lines (and how they are chosen), the box
 * layout of the periodic VAT return, the recapitulative statement and the
 * reporting calendar. The core computes a return by summing the journal's tax
 * codes as the layout says and files the result; the layout is data, not code.
 */
interface ReturnsProfile
{
    /**
     * The returns the profile can produce.
     *
     * @return list<ReturnKind>
     */
    public function kinds(): array;

    /**
     * Every tax code the booking services stamp on journal lines.
     *
     * @return list<TaxCodeInfo>
     */
    public function taxCodes(): array;

    /**
     * The tax code of a journal line by purpose, scenario code (outgoing for
     * `RevenueBase` and `OutputVat`, incoming for the input-side purposes,
     * `de_domestic`-style core codes for withdrawals) and rate key. `$basis` only
     * matters for `OutputVat`. null when the combination carries no code (an
     * exempt scenario has no VAT line; an unknown scenario has no base code).
     */
    public function taxCodeFor(TaxCodePurpose $purpose, string $scenario, string $rateKey, ?VatBasis $basis = null): ?string;

    /**
     * Sign convention of a tax code, also for codes `taxCodes()` does not list:
     * true when the lines of the code read as debit minus credit.
     */
    public function isDebitPositive(string $taxCode): bool;

    /**
     * The sections and boxes of the periodic VAT return; empty when the profile
     * has none.
     *
     * @return list<ReturnSection>
     */
    public function vatAdvanceLayout(): array;

    /**
     * Figures reported next to the boxes (OSS figures, VAT not yet due).
     *
     * @return list<ReturnInfoLine>
     */
    public function vatAdvanceInfo(): array;

    /**
     * The recapitulative statement, or null when the profile has none.
     */
    public function recapitulativeLayout(): ?RecapitulativeLayout;

    public function periodRules(): ReturnPeriodRules;
}
