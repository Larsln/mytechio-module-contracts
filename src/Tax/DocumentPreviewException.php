<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Tax;

use MyTechIO\Contracts\ContractException;

/**
 * Thrown when a document cannot be previewed: the contact, a tax category or
 * an article of a line is unknown, or the core has no tax rate for a line
 * (German message for staff).
 */
final class DocumentPreviewException extends ContractException {}
