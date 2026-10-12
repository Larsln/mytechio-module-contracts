<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use DateTimeImmutable;
use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Accounting\FiscalPeriodData;
use MyTechIO\Contracts\Accounting\FiscalYearBoundaries;
use MyTechIO\Contracts\Accounting\FiscalYearData;
use MyTechIO\Contracts\Accounting\FiscalYears;
use MyTechIO\Contracts\Accounting\FiscalYearStatus;
use MyTechIO\Contracts\Accounting\FiscalYearStatusData;
use MyTechIO\Contracts\ContractException;

/**
 * Test double for `FiscalYears`: an in-memory fiscal-year calendar with the
 * core's rules for the year-end close (all 13 periods locked, every earlier
 * year with bookings closed first) and the period locks `isPeriodOpen()`
 * answers from. `lockPeriod()` locks one period, `lockYear()` all 13,
 * `seedBookedYear()` marks a year as having bookings; the fiscal year starts
 * in `$startMonth` (1 = calendar year). `events()` lists the domain events
 * the real implementation would have fired, as `['event' => name, 'payload'
 * => array]`.
 */
final class FakeFiscalYears implements FiscalYears
{
    /**
     * @var array<int, array<int, bool>>
     */
    private array $locks = [];

    /**
     * @var array<int, array{closedAt: string, closedBy: string}>
     */
    private array $closed = [];

    /**
     * @var array<int, true>
     */
    private array $booked = [];

    /**
     * @var list<array{event: string, payload: array<string, mixed>}>
     */
    private array $events = [];

    public function __construct(
        private readonly int $startMonth = 1,
    ) {}

    public function lockPeriod(int $year, int $periodNo): void
    {
        $this->locks[$year][$periodNo] = true;
        $this->booked[$year] = true;
    }

    public function lockYear(int $year): void
    {
        foreach (range(1, 13) as $periodNo) {
            $this->lockPeriod($year, $periodNo);
        }
    }

    public function seedBookedYear(int $year): void
    {
        $this->booked[$year] = true;
    }

    public function list(): array
    {
        $years = array_unique([...array_keys($this->booked), ...array_keys($this->closed), $this->currentYear()]);
        rsort($years);

        return array_map(fn (int $year): FiscalYearData => $this->find($year), $years);
    }

    public function find(int $year): FiscalYearData
    {
        $boundaries = $this->boundaries($year);
        $closed = $this->closed[$year] ?? null;
        $lockedPeriods = count($this->locks[$year] ?? []);

        return new FiscalYearData(
            year: $year,
            startsOn: $boundaries->startsOn,
            endsOn: $boundaries->endsOn,
            status: $closed === null ? FiscalYearStatus::Open : FiscalYearStatus::Closed,
            closedPeriods: $lockedPeriods,
            isClosable: $closed === null && $lockedPeriods === 13 && $this->priorYearsClosed($year),
            resultAmount: $closed === null ? null : '0.0000',
            closedAt: $closed['closedAt'] ?? null,
            closedByLabel: $closed['closedBy'] ?? null,
        );
    }

    public function current(): FiscalYearData
    {
        return $this->find($this->currentYear());
    }

    public function boundaries(int $year): FiscalYearBoundaries
    {
        $start = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $this->startMonth));

        return new FiscalYearBoundaries(
            $year,
            $start->format('Y-m-d'),
            $start->modify('+12 months')->modify('-1 day')->format('Y-m-d'),
        );
    }

    public function status(int $year): FiscalYearStatusData
    {
        $start = new DateTimeImmutable($this->boundaries($year)->startsOn);
        $lastDay = $start->modify('+12 months')->modify('-1 day');
        $periods = [];

        foreach (range(1, 13) as $periodNo) {
            $from = $periodNo === 13 ? $lastDay : $start->modify('+'.($periodNo - 1).' months');
            $to = $periodNo === 13 ? $lastDay : $from->modify('last day of this month');

            $periods[] = new FiscalPeriodData(
                $periodNo,
                $from->format('Y-m-d'),
                $to->format('Y-m-d'),
                isset($this->locks[$year][$periodNo]) ? FiscalYearStatus::Closed : FiscalYearStatus::Open,
            );
        }

        return new FiscalYearStatusData($year, isset($this->closed[$year]) ? FiscalYearStatus::Closed : FiscalYearStatus::Open, $periods);
    }

    public function isPeriodOpen(string $date): bool
    {
        $day = new DateTimeImmutable($date);
        $year = $this->fiscalYearOf($day);

        if (isset($this->closed[$year])) {
            return false;
        }

        $periodNo = (((int) $day->format('n') - $this->startMonth + 12) % 12) + 1;

        return ! isset($this->locks[$year][$periodNo]);
    }

    public function close(int $year, ActorRef $actor): FiscalYearData
    {
        if (isset($this->closed[$year])) {
            throw new ContractException("Das Geschäftsjahr {$year} ist bereits abgeschlossen.");
        }

        $open = array_values(array_diff(range(1, 13), array_keys($this->locks[$year] ?? [])));

        if ($open !== []) {
            throw new ContractException(sprintf('Vor dem Jahresabschluss müssen alle Perioden festgeschrieben sein — noch offen: %s.', implode(', ', $open)));
        }

        foreach (array_keys($this->booked) as $prior) {
            if ($prior < $year && ! isset($this->closed[$prior])) {
                throw new ContractException("Das frühere Geschäftsjahr {$prior} enthält Buchungen und muss zuerst abgeschlossen werden.");
            }
        }

        $this->closed[$year] = ['closedAt' => date('c'), 'closedBy' => $actor->label];
        $this->events[] = ['event' => 'FiscalYearClosed', 'payload' => ['fiscal_year' => $year, 'result_amount' => '0.0000']];

        return $this->find($year);
    }

    /**
     * @return list<array{event: string, payload: array<string, mixed>}>
     */
    public function events(): array
    {
        return $this->events;
    }

    private function currentYear(): int
    {
        return $this->fiscalYearOf(new DateTimeImmutable('today'));
    }

    private function fiscalYearOf(DateTimeImmutable $day): int
    {
        $year = (int) $day->format('Y');

        return (int) $day->format('n') >= $this->startMonth ? $year : $year - 1;
    }

    private function priorYearsClosed(int $year): bool
    {
        foreach (array_keys($this->booked) as $prior) {
            if ($prior < $year && ! isset($this->closed[$prior])) {
                return false;
            }
        }

        return true;
    }
}
