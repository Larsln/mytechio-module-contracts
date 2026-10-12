<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Fiscal;

/**
 * The hand-over exports of the profile (accountant formats). A profile lists
 * the kinds it can produce; the German profile lists none until its DATEV
 * export exists.
 */
interface Exports
{
    /**
     * @return list<ExportKind>
     */
    public function kinds(): array;
}
