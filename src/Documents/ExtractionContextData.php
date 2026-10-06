<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Kontext für `InvoiceExtractor::extract()`: die aktiven Aufwandskonten für
 * Kontovorschläge sowie die kanonische Einheitenliste, damit das Modell
 * gedruckte Einheiten auf Standard-Codes mappt (Freitext nur als Ausnahme).
 * Bewusst OHNE Kontaktliste — das Lieferanten-Matching läuft im Kern,
 * niemals im Modell.
 */
final readonly class ExtractionContextData
{
    /**
     * @param  list<array{number: string, name: string, category: ?string}>  $expenseAccounts
     * @param  list<string>  $units
     */
    public function __construct(
        public array $expenseAccounts,
        public array $units,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'expense_accounts' => $this->expenseAccounts,
            'units' => $this->units,
        ];
    }
}
