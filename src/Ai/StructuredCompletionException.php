<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Ai;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a `StructuredCompletion` provider cannot deliver a result
 * that matches the requested schema (transport error, blocked or aborted
 * generation, undecodable output). The message is a short German text for
 * the person; technical details belong in the provider's log.
 */
final class StructuredCompletionException extends ContractException {}
