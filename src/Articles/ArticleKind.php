<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * Kind of a catalog article.
 *
 * There are exactly three kinds. "Recurring" is not a kind but a period price
 * option of an article, and a set (bundle) is an attribute of an article, not
 * a kind.
 */
enum ArticleKind: string
{
    case Goods = 'goods';
    case Service = 'service';
    case ElectronicService = 'electronic_service';

    public function label(): string
    {
        return match ($this) {
            self::Goods => 'Ware',
            self::Service => 'Dienstleistung',
            self::ElectronicService => 'Elektronische Leistung',
        };
    }

    /**
     * The tax classification an article of this kind is pre-filled with.
     */
    public function defaultTaxClassification(): TaxClassification
    {
        return match ($this) {
            self::Goods => TaxClassification::SupplyOfGoods,
            self::Service => TaxClassification::OtherService,
            self::ElectronicService => TaxClassification::ElectronicService,
        };
    }
}
