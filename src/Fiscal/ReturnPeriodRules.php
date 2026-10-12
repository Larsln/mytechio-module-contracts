<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The reporting calendar of the profile's returns: which frequencies a return
 * can be filed in and the periods with their filing deadlines.
 */
interface ReturnPeriodRules
{
    /**
     * Filing frequencies the profile offers (e.g. `month`, `quarter`).
     *
     * @return list<string>
     */
    public function frequencies(): array;

    /**
     * The periods of a calendar year for a return kind (`ReturnKind::VAT_ADVANCE`, `ReturnKind::RECAPITULATIVE`).
     * `$extended` applies the profile's deadline extension where the return has one.
     *
     * @return list<ReturnPeriod>
     *
     * @throws \InvalidArgumentException for an unknown return kind or frequency
     */
    public function periods(string $returnKind, int $calendarYear, string $frequency, bool $extended): array;
}
