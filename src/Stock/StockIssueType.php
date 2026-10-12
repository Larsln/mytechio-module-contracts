<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Stock;

/**
 * What `Stock::recordIssue()` records for a document: `Sale` takes the goods
 * out of the stock (and snapshots the cost of goods sold), `Return` is a
 * customer return that puts them back.
 */
enum StockIssueType: string
{
    case Sale = 'sale';
    case Return = 'return';
}
