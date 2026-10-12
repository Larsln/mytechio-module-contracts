<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A kind of supply on the recapitulative statement (goods, services): its
 * label, the indicator column of the statement and the outgoing scenarios whose
 * lines are reported under it.
 */
final readonly class RecapitulativeKind
{
    public const string GOODS = 'goods';

    public const string SERVICE = 'service';

    /**
     * @param  list<string>  $scenarios  Outgoing scenario codes.
     */
    public function __construct(
        public string $code,
        public string $label,
        public string $indicator,
        public array $scenarios,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['code' => $this->code, 'label' => $this->label, 'indicator' => $this->indicator, 'scenarios' => $this->scenarios];
    }
}
