<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * A booking role the core resolves to a ledger account through the profile's
 * `AccountMap`. Roles that depend on the tax rate take the normalized rate key
 * (`19` for 19.00 %, `7`, `20.5`), roles that depend on the scenario take the
 * scenario code; the others take neither.
 */
enum AccountRole: string
{
    /** Trade receivables: debited by an outgoing invoice, credited by its payment. */
    case Receivable = 'receivable';

    /** Trade payables: credited by an incoming invoice, debited by its payment. */
    case Payable = 'payable';

    /** Revenue deductions: customer discounts on outgoing invoices. */
    case Discount = 'discount';

    /** Clearing account bridging the two statement lines of a transfer between own bank accounts. */
    case Transit = 'transit';

    /** Account that carries the year result into the following year. */
    case RetainedEarnings = 'retained_earnings';

    /** Account of the incidental acquisition costs of an incoming invoice. */
    case AcquisitionCost = 'acquisition_cost';

    /** Output VAT that is already due (accrual basis), by scenario and rate. */
    case OutputVatDue = 'output_vat_due';

    /** Output VAT that is not yet due (cash basis until the payment arrives), by scenario and rate. */
    case OutputVatNotYetDue = 'output_vat_not_yet_due';

    /** Deductible input VAT of an incoming invoice, by incoming scenario and rate. */
    case InputVat = 'input_vat';

    /** Output VAT liability of a self-assessed incoming invoice (reverse charge, acquisition), by incoming scenario and rate. */
    case ReverseChargeVat = 'reverse_charge_vat';
}
