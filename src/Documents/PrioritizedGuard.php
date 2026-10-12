<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

/**
 * Optional addition to an `IncomingInvoiceGuard` that needs to run before or
 * after the others. The core calls guards in ascending `priority()`; guards
 * without this interface count as priority `0`, equal priorities keep the
 * order in which they were tagged. The core's own purchase-price recording
 * runs at `-100`, so every module guard sees its results.
 */
interface PrioritizedGuard
{
    public function priority(): int;
}
