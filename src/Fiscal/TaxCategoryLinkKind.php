<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * What a link between a tax category and an account means.
 */
enum TaxCategoryLinkKind: string
{
    /** The account carries the category as its default tax category. */
    case AccountDefault = 'account_default';

    /** The account is the category's default revenue account. */
    case CategoryRevenue = 'category_revenue';

    /** The account is the category's default expense account. */
    case CategoryExpense = 'category_expense';
}
