<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Pricing;

/**
 * Where the price of a customer came from, in the order the core checks:
 * the more specific an agreement, the earlier it wins. Mirrors the core's
 * enum value for value.
 */
enum CustomerPriceSource: string
{
    case OptionFixed = 'option_fixed';
    case ArticleFixed = 'article_fixed';
    case OptionPercent = 'option_percent';
    case ArticlePercent = 'article_percent';
    case GroupPercent = 'group_percent';
    case ListPercent = 'list_percent';
    case CustomerDefault = 'customer_default';
    case None = 'none';
}
