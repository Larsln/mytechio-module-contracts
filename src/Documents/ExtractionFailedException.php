<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Documents;

use MyTechIO\Contracts\ContractException;

/**
 * Wird geworfen, wenn ein `InvoiceExtractor` keine verwertbare Payload
 * liefern kann (Transportfehler, blockierte/abgebrochene Generierung,
 * undekodierbarer Output oder ein für die Inline-Analyse zu großes
 * Dokument). Die Message ist ein kurzer deutscher Text für die
 * Posteingang-Anzeige — technische Details gehören ins Log des Moduls,
 * nicht in diese Message.
 */
final class ExtractionFailedException extends ContractException {}
