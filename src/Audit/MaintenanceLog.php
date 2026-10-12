<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Audit;

use MyTechIO\Contracts\Accounting\ActorRef;

/**
 * The core's hash-chained break-glass log for superadmin actions that bypass a
 * protection (for example a write-off that takes the stock negative). It is
 * separate from the normal audit log and append-only; modules only append,
 * reading and verifying the chain stay with the core.
 */
interface MaintenanceLog
{
    /**
     * Appends one action to the chain. Call it inside the transaction of the
     * business change so both commit or roll back together.
     *
     * @param  string  $action  Dotted action key, e.g. `stock.negative_override`.
     * @param  string|null  $subjectType  What the action touched (the stored class name or a type key), null for none.
     * @param  array<string, mixed>  $context  JSON scalars and arrays only, no floats (money and quantities as strings).
     */
    public function record(ActorRef $actor, string $action, ?string $subjectType = null, int|string|null $subjectId = null, array $context = []): void;
}
