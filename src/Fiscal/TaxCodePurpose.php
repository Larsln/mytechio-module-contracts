<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * What a journal line stamped with a tax code stands for. The booking services
 * ask the profile's `ReturnsProfile::taxCodeFor()` for the code of a line by
 * purpose, scenario and rate key; the VAT return reads the lines back by code.
 */
enum TaxCodePurpose: string
{
    /** Tax base of an outgoing revenue line (and of its revenue deductions), by outgoing scenario and rate key. */
    case RevenueBase = 'revenue_base';

    /** Output VAT of an outgoing line, by outgoing scenario, rate key and VAT basis (cash basis: not yet due). */
    case OutputVat = 'output_vat';

    /** Tax base of an incoming expense line, by incoming scenario and rate key. */
    case ExpenseBase = 'expense_base';

    /** Deductible input VAT of an incoming invoice, by incoming scenario and rate key. */
    case InputVat = 'input_vat';

    /** Output VAT liability a self-assessed incoming invoice (reverse charge, acquisition) creates, by incoming scenario and rate key. */
    case ReverseChargeVat = 'reverse_charge_vat';

    /** Tax base of a free-of-charge transfer (withdrawal), taxed on the accrual basis. */
    case WithdrawalBase = 'withdrawal_base';

    /** Output VAT of a free-of-charge transfer (withdrawal). */
    case WithdrawalVat = 'withdrawal_vat';
}
