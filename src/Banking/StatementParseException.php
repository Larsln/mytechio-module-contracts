<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Banking;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a statement source cannot turn a payload into a
 * `ParsedStatementSet` (unreadable file, unsupported layout, rejected
 * amount or date, failed fetch) or when a parsed DTO is malformed. The
 * message is a short German text shown to the person who uploaded the
 * file; technical details belong in the source's log.
 */
final class StatementParseException extends ContractException {}
