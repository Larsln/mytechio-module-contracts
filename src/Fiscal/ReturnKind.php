<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A tax return the profile can produce. The three codes below are the kinds the
 * core knows how to compute and file; a profile lists those it supports.
 */
final readonly class ReturnKind
{
    /** Periodic VAT return (German UStVA): boxes computed from the journal's tax codes. */
    public const string VAT_ADVANCE = 'vat_advance';

    /** Recapitulative statement of intra-Community supplies (German ZM), computed from outgoing invoices. */
    public const string RECAPITULATIVE = 'recapitulative';

    /** One-stop-shop return; the core reports its figures as info lines of the VAT return. */
    public const string OSS = 'oss';

    public function __construct(
        public string $code,
        public string $name,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['code' => $this->code, 'name' => $this->name];
    }
}
