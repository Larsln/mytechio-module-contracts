<?php

declare(strict_types=1);

namespace MyTechIO\Contracts;

/**
 * Version information for the contracts package.
 *
 * The core and modules compare themselves against `VERSION` during module
 * registration to check compatibility according to Semver: additive changes
 * (new methods with default behavior in the fakes, new DTO fields with a
 * default) bump the minor version, signature changes bump the major version.
 */
final class Contracts
{
    public const string VERSION = '1.7.0';
}
