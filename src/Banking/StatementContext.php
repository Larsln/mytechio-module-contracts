<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Banking;

/**
 * What the core knows about the target account when it asks a source to
 * parse or fetch: the IBAN and currency of the account and the options the
 * person stored for the source (e.g. a CSV profile name).
 */
final readonly class StatementContext
{
    /**
     * @param  string|null  $accountIban  normalised IBAN (upper case, no spaces); null for accounts without one
     * @param  string  $currency  ISO 4217 code of the account
     * @param  array<string, mixed>  $options  source-specific options, opaque to the core
     */
    public function __construct(
        public ?string $accountIban,
        public string $currency,
        public array $options = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'account_iban' => $this->accountIban,
            'currency' => $this->currency,
            'options' => $this->options,
        ];
    }
}
