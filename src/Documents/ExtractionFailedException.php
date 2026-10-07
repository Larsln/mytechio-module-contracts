<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when an `InvoiceExtractor` cannot deliver a usable payload
 * (transport error, blocked/aborted generation, undecodable output, or a
 * document too large for inline analysis). The message is a short German
 * text for the incoming-document inbox display — technical details belong
 * in the module's log, not in this message.
 */
final class ExtractionFailedException extends ContractException {}
