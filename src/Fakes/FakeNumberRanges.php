<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fakes;

use MyTechIO\Contracts\Accounting\ActorRef;
use MyTechIO\Contracts\Accounting\NumberRangeData;
use MyTechIO\Contracts\Accounting\NumberRangeNotFoundException;
use MyTechIO\Contracts\Accounting\NumberRanges;
use MyTechIO\Contracts\Accounting\NumberRangeSettings;
use MyTechIO\Contracts\Accounting\NumberRangeVersion;
use MyTechIO\Contracts\ContractException;

/**
 * Test double for `NumberRanges`: in-memory ranges with a counter per range
 * and year (one counter for ranges without a year in the number), the core's
 * format rules for scheduled changes (no change of locked ranges, only future
 * years, no dropping of the year once numbers were issued) and the
 * gaplessness of the draw within one test. `seed()` sets a range up;
 * `events()` lists the domain events the real implementation would have
 * fired, as `['event' => name, 'payload' => array]`.
 */
final class FakeNumberRanges implements NumberRanges
{
    /**
     * @var array<string, NumberRangeData>
     */
    private array $ranges = [];

    /**
     * @var array<string, int>
     */
    private array $counters = [];

    /**
     * @var list<array{event: string, payload: array<string, mixed>}>
     */
    private array $events = [];

    public function seed(NumberRangeData $range): void
    {
        $this->ranges[$range->type] = $range;
        $this->counters[$this->counterKey($range, (int) date('Y'))] = $range->nextNumber;
    }

    public function list(): array
    {
        return array_values($this->ranges);
    }

    public function find(string $type): ?NumberRangeData
    {
        $range = $this->ranges[$type] ?? null;

        return $range === null ? null : $this->withCounter($range);
    }

    public function preview(string $type, int $year): string
    {
        $range = $this->get($type);

        return self::format($range, $year, $this->counters[$this->counterKey($range, $year)] ?? 1);
    }

    public function next(string $type, int $year): string
    {
        $range = $this->get($type);
        $key = $this->counterKey($range, $year);
        $number = $this->counters[$key] ?? 1;
        $this->counters[$key] = $number + 1;

        $formatted = self::format($range, $year, $number);

        if ($year === (int) date('Y')) {
            $this->ranges[$type] = $this->withCounter($range, used: true);
        }

        $this->events[] = ['event' => 'NumberDrawn', 'payload' => ['type' => $type, 'year' => $year, 'number' => $formatted]];

        return $formatted;
    }

    public function update(string $type, NumberRangeSettings $settings, ActorRef $actor): NumberRangeData
    {
        $range = $this->get($type);
        $currentYear = (int) date('Y');

        if ($range->isLocked) {
            throw new ContractException('Artikel-, Kunden- und Lieferantennummern sind nach der Einrichtung dauerhaft fix.');
        }

        if ($settings->validFromYear < $currentYear + 1 || $settings->validFromYear > $currentYear + 5) {
            throw new ContractException('Format-Änderungen wirken frühestens ab dem nächsten Jahr — die laufende Serie bleibt unverändert.');
        }

        if ($settings->padding < 1 || $settings->padding > 10 || strlen($settings->prefix) > 10) {
            throw new ContractException('Präfix (höchstens 10 Zeichen) oder Stellenzahl (1 bis 10) ist ungültig.');
        }

        if ($range->includeYear && ! $settings->includeYear && $range->isUsed) {
            throw new ContractException('Die Jahreszahl kann nach der ersten vergebenen Nummer nicht mehr entfernt werden.');
        }

        if ($range->inCollisionGroup && trim($settings->prefix) === '') {
            throw new ContractException('Rechnung, Storno und Gutschrift schreiben in dieselbe Nummern-Spalte — ein Präfix ist hier Pflicht.');
        }

        $pending = array_values(array_filter(
            $range->pending,
            static fn (NumberRangeVersion $version): bool => $version->validFromYear !== $settings->validFromYear,
        ));
        $prefix = trim($settings->prefix);
        $pending[] = new NumberRangeVersion(
            $settings->validFromYear,
            $prefix,
            $settings->padding,
            $settings->includeYear,
            self::formatWith($prefix, $settings->padding, $settings->includeYear, $settings->validFromYear, 1),
        );
        usort($pending, static fn (NumberRangeVersion $a, NumberRangeVersion $b): int => $a->validFromYear <=> $b->validFromYear);

        $this->ranges[$type] = $this->copy($range, pending: $pending);
        $this->events[] = ['event' => 'NumberRangeUpdated', 'payload' => ['type' => $type, 'action' => 'scheduled', 'valid_from_year' => $settings->validFromYear]];

        return $this->ranges[$type];
    }

    public function discardScheduled(string $type, int $validFromYear, ActorRef $actor): NumberRangeData
    {
        $range = $this->get($type);

        if ($validFromYear <= (int) date('Y')) {
            throw new ContractException('Nur geplante (zukünftige) Format-Versionen können verworfen werden.');
        }

        $this->ranges[$type] = $this->copy($range, pending: array_values(array_filter(
            $range->pending,
            static fn (NumberRangeVersion $version): bool => $version->validFromYear !== $validFromYear,
        )));
        $this->events[] = ['event' => 'NumberRangeUpdated', 'payload' => ['type' => $type, 'action' => 'discarded', 'valid_from_year' => $validFromYear]];

        return $this->ranges[$type];
    }

    /**
     * @return list<array{event: string, payload: array<string, mixed>}>
     */
    public function events(): array
    {
        return $this->events;
    }

    private function get(string $type): NumberRangeData
    {
        return $this->ranges[$type] ?? throw new NumberRangeNotFoundException($type);
    }

    private function counterKey(NumberRangeData $range, int $year): string
    {
        return $range->type.':'.($range->includeYear ? $year : 'all');
    }

    private function withCounter(NumberRangeData $range, bool $used = false): NumberRangeData
    {
        $next = $this->counters[$this->counterKey($range, (int) date('Y'))] ?? $range->nextNumber;

        return new NumberRangeData(
            $range->type,
            $range->label,
            $range->prefix,
            $range->padding,
            $range->includeYear,
            $next,
            self::format($range, (int) date('Y'), $next),
            $range->isLocked,
            $range->isUsed || $used,
            $range->inCollisionGroup,
            $range->pending,
        );
    }

    /**
     * @param  list<NumberRangeVersion>  $pending
     */
    private function copy(NumberRangeData $range, array $pending): NumberRangeData
    {
        return new NumberRangeData(
            $range->type,
            $range->label,
            $range->prefix,
            $range->padding,
            $range->includeYear,
            $range->nextNumber,
            $range->preview,
            $range->isLocked,
            $range->isUsed,
            $range->inCollisionGroup,
            $pending,
        );
    }

    private static function format(NumberRangeData $range, int $year, int $number): string
    {
        return self::formatWith($range->prefix, $range->padding, $range->includeYear, $year, $number);
    }

    private static function formatWith(string $prefix, int $padding, bool $includeYear, int $year, int $number): string
    {
        $padded = str_pad((string) $number, $padding, '0', STR_PAD_LEFT);

        return $includeYear ? sprintf('%s%d-%s', $prefix, $year, $padded) : $prefix.$padded;
    }
}
