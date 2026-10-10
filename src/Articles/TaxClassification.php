<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * VAT classification of what an article supplies.
 *
 * A supply of goods is subject to the rules for intra-Community supply and
 * export when sold to an EU business customer; an other service is subject to
 * the reverse charge for EU business customers; an electronically supplied
 * service is taxed at the place of the consumer for EU private customers.
 *
 * The classification is pre-filled from `ArticleKind::defaultTaxClassification()`
 * and can be overridden per article. The core derives the tax scenario of a
 * document line from it together with the customer.
 */
enum TaxClassification: string
{
    case SupplyOfGoods = 'supply_of_goods';
    case OtherService = 'other_service';
    case ElectronicService = 'electronic_service';

    public function label(): string
    {
        return match ($this) {
            self::SupplyOfGoods => 'Lieferung',
            self::OtherService => 'Sonstige Leistung',
            self::ElectronicService => 'Elektronisch erbrachte Leistung',
        };
    }
}
