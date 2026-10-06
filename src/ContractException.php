<?php

declare(strict_types=1);

namespace MyTechIO\Contracts;

/**
 * Basisklasse aller Ausnahmen des Vertragspakets.
 *
 * Module können sich wahlweise gegen die spezifischen Unterklassen (z. B.
 * `Accounting\UnbalancedEntryException`) oder pauschal gegen
 * `ContractException` absichern, wenn nur „irgendein Vertragsfehler"
 * relevant ist.
 */
class ContractException extends \RuntimeException {}
