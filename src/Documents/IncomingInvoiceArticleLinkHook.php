<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

use MyTechIO\Contracts\Accounting\ActorRef;

/**
 * Optional addition to an `IncomingInvoiceGuard`: the core also calls it when a
 * line of an already booked incoming invoice is linked to an article
 * afterwards ("link article" on the document page). The call runs inside the
 * core's transaction after the link was saved; the receiving side (for
 * example the stock module) books the goods receipt for exactly this line.
 */
interface IncomingInvoiceArticleLinkHook
{
    /**
     * `$item` carries the acquisition fields (`receivedQuantity`,
     * `unitCostBase`, `unitIncidentalCost`, `economicDate`) computed for this
     * line alone; they are `null` when the line contributes no goods receipt.
     */
    public function afterArticleLinked(IncomingInvoiceRef $invoice, IncomingInvoiceItemRef $item, ActorRef $actor): void;
}
