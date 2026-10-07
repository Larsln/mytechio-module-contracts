<?php

declare(strict_types=1);

namespace MyTechIO\Contracts;

/**
 * Base class for all exceptions in the contracts package.
 *
 * Modules can catch either the specific subclasses (e.g.
 * `Accounting\UnbalancedEntryException`) or, more broadly,
 * `ContractException` when only "some kind of contract error"
 * is relevant.
 */
class ContractException extends \RuntimeException {}
