<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The tax rules of a fiscal profile: the place-of-supply rule table, the
 * rates, the revenue and expense accounts per scenario, the expense-account
 * restrictions of incoming scenarios and the seed data of the
 * user-maintainable tax tables.
 *
 * Scenario codes are strings (`TaxScenarioInfo::$code`); dates are ISO dates
 * (`Y-m-d`); rates are decimal strings such as `19.00`. The core calls these
 * methods for every outgoing line and every incoming invoice, so they must be
 * deterministic for the same inputs and the same reference data.
 */
interface TaxRules
{
    /**
     * The region of a customer's country relative to the profile's country.
     */
    public function regionOf(string $countryCode): CustomerRegion;

    /**
     * The rule table for one line: automatic scenario, the scenarios a manual
     * deviation may choose from, explanation and editor flags.
     */
    public function decideScenario(CustomerTaxContext $customer, LineTaxContext $line): ScenarioDecision;

    /**
     * One-line description of the customer as the rule table sees it.
     */
    public function describeCustomer(CustomerTaxContext $customer): string;

    /**
     * Rate of a scenario for a tax category as of a date: the category's rate
     * for taxed domestic scenarios, 0.00 for the exempt ones, the
     * destination-country rate for OSS-like scenarios. null when the profile
     * has no rate for the combination.
     */
    public function rate(string $scenario, string $countryCode, string $taxCategoryCode, string $date): ?string;

    /**
     * Rates of a country as of a date, by tax category code.
     *
     * @return array<string, string>
     */
    public function ratesFor(string $countryCode, string $date): array;

    /**
     * Revenue account of an outgoing scenario and tax category.
     */
    public function revenueAccount(string $scenario, string $taxCategoryCode): string;

    /**
     * Default expense account of an incoming scenario and tax category, or null
     * when the profile names none.
     *
     * @deprecated since 1.11.0 The core ignores it; bindings on the accounts replace it.
     */
    public function expenseAccount(string $incomingScenario, string $taxCategoryCode): ?string;

    /**
     * Incoming scenarios restricted to exactly these expense accounts.
     *
     * @deprecated since 1.11.0 The core ignores it; the bindings on the accounts
     *             (`ChartOfAccounts::autoBookingLinks()` with incoming scenarios) replace it.
     *
     * @return array<string, list<string>> incoming scenario code => account numbers
     */
    public function restrictedExpenseAccounts(): array;

    /**
     * Expense accounts reserved for one incoming scenario and blocked in every other.
     *
     * @deprecated since 1.11.0 The core ignores it; the bindings on the accounts
     *             (`ChartOfAccounts::autoBookingLinks()` with incoming scenarios) replace it.
     *
     * @return array<string, list<string>> incoming scenario code => account numbers
     */
    public function exclusiveExpenseAccounts(): array;

    /**
     * Seed data of the tax categories.
     *
     * @return list<TaxCategoryInfo>
     */
    public function taxCategories(): array;

    /**
     * Seed data of the tax rates.
     *
     * @return list<TaxRateRow>
     */
    public function taxRateTable(): array;
}
