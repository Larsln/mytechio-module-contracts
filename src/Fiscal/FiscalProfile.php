<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * Everything country-specific about bookkeeping and taxes: the tax scenarios
 * with their texts, the tax rules, the account map and the chart of accounts,
 * the returns, the closing, the electronic invoice formats and the exports.
 * The core ships the German profile (`de`); a module can
 * bring another one by tagging its implementation `mytechio.fiscal_profiles`.
 *
 * The tenant chooses exactly one profile when it is set up and the choice is
 * fixed afterwards: `FiscalProfiles::current()` resolves it. A profile is
 * stateless; its answers may depend on the seeded reference data (tax
 * categories and tax rates are user-maintainable tables of the core).
 */
interface FiscalProfile
{
    /**
     * Stable identifier stored with the tenant, e.g. `de`.
     */
    public function code(): string;

    /**
     * Human-readable name for the setup, e.g. `Deutschland (SKR04)`.
     */
    public function name(): string;

    /**
     * ISO 3166-1 alpha-2 country of the profile: the seller's country that
     * makes a customer "domestic".
     */
    public function country(): string;

    /**
     * ISO 4217 currency of the books.
     */
    public function currency(): string;

    /**
     * The outgoing tax scenarios the profile knows.
     *
     * @return list<TaxScenarioInfo>
     */
    public function scenarios(): array;

    /**
     * @throws UnknownScenarioException
     */
    public function scenario(string $code): TaxScenarioInfo;

    /**
     * The tax scenarios of incoming invoices.
     *
     * @return list<TaxScenarioInfo>
     */
    public function incomingScenarios(): array;

    /**
     * @throws UnknownScenarioException
     */
    public function incomingScenario(string $code): TaxScenarioInfo;

    public function taxRules(): TaxRules;

    public function chart(): ChartOfAccounts;

    public function returns(): ReturnsProfile;

    public function closing(): ClosingProfile;

    public function invoiceFormats(): InvoiceFormats;

    public function exports(): Exports;
}
