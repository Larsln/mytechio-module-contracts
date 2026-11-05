<?php

declare(strict_types=1);

namespace MyTechIO\Contracts;

/**
 * Version information for the contracts package.
 *
 * `VERSION` names the released package version for diagnostics (module health
 * pages, bug reports); compatibility itself is enforced by the Composer
 * constraints of the core and the modules. Semver: additive changes (new
 * methods with default behavior in the fakes, new DTO fields with a default)
 * bump the minor version, signature changes bump the major version.
 */
final class Contracts
{
    public const string VERSION = '2.12.0';
}
