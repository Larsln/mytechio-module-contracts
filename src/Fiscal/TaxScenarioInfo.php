<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The profile's texts and flags for one tax scenario. The scenario codes
 * themselves (`de_domestic`, `eu_b2b_igl`, ... for outgoing lines, `rc_13b`,
 * `ig_erwerb`, ... for incoming ones) are the core's EU-wide vocabulary; what
 * a profile owns is how a scenario is worded on documents and in the editor
 * and whether it is supported at all.
 *
 * `chipLabel` is the compact label of a line's treatment with the placeholders
 * `{rate}` (the line's rate as "19 %") and `{country}` (the customer's country
 * code), see `chip()`. `invoiceNote` is the statutory note on the document
 * (null where VAT is shown normally); `reasonNotes` replaces it by reason code
 * where the legal basis of the treatment varies (a service that is not taxable
 * names its basis), and `zeroRateNote` is the note of a taxed scenario whose
 * rate turned out to be 0 %. `warnings` are editor warnings by reason code
 * (`WARNING_*`). `exportProofRequired` flags scenarios whose exemption needs a
 * proof of export. A scenario with `supported = false` is refused by the core
 * wherever it would have to be used.
 */
final readonly class TaxScenarioInfo
{
    public const string WARNING_SMALL_BUSINESS = 'small_business';

    public const string WARNING_VAT_ID_INVALID = 'vat_id_invalid';

    /**
     * @param  array<string, string>  $reasonNotes  reason code => note
     * @param  array<string, string>  $warnings  reason code => warning text
     */
    public function __construct(
        public string $code,
        public string $label,
        public string $description,
        public string $chipLabel,
        public ?string $abbreviation = null,
        public ?string $invoiceNote = null,
        public array $reasonNotes = [],
        public ?string $zeroRateNote = null,
        public array $warnings = [],
        public bool $exportProofRequired = false,
        public bool $supported = true,
    ) {}

    /**
     * The document note for an optional reason code: the reason's note where
     * the profile has one, otherwise the scenario's own note.
     */
    public function noteFor(?string $reason = null): ?string
    {
        return $reason !== null && isset($this->reasonNotes[$reason])
            ? $this->reasonNotes[$reason]
            : $this->invoiceNote;
    }

    /**
     * Every note this scenario can put on a document (not the zero-rate note),
     * to tell free-text document notes apart from scenario notes.
     *
     * @return list<string>
     */
    public function standardNotes(): array
    {
        return array_values(array_unique(array_filter(
            [$this->invoiceNote, ...array_values($this->reasonNotes)],
            fn (?string $note): bool => $note !== null,
        )));
    }

    /**
     * The chip label for a line: `{rate}` becomes the decimal rate string
     * without trailing zeros and with a decimal comma ("19 %", "5,5 %"),
     * `{country}` the upper-cased country code.
     */
    public function chip(string $rate, string $countryCode): string
    {
        $percent = str_contains($rate, '.') ? rtrim(rtrim($rate, '0'), '.') : $rate;

        return strtr($this->chipLabel, [
            '{rate}' => str_replace('.', ',', $percent).' %',
            '{country}' => strtoupper($countryCode),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->label,
            'description' => $this->description,
            'chip_label' => $this->chipLabel,
            'abbreviation' => $this->abbreviation,
            'invoice_note' => $this->invoiceNote,
            'reason_notes' => $this->reasonNotes,
            'zero_rate_note' => $this->zeroRateNote,
            'warnings' => $this->warnings,
            'export_proof_required' => $this->exportProofRequired,
            'supported' => $this->supported,
        ];
    }
}
