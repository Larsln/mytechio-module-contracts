<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * When output VAT becomes due: with the invoice (accrual basis, `soll`) or only
 * with the payment (cash basis, `ist`). The values are the ones the core stores
 * with the organization and its documents.
 */
enum VatBasis: string
{
    case Accrual = 'soll';

    case Cash = 'ist';
}
