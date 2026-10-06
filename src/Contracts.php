<?php

declare(strict_types=1);

namespace MyTechIO\Contracts;

/**
 * Versionsinformation des Vertragspakets.
 *
 * Kern und Module vergleichen sich bei der Modul-Registrierung gegen
 * `VERSION`, um Kompatibilität nach Semver zu prüfen: additive Änderungen
 * (neue Methoden mit Default-Verhalten in den Fakes, neue DTO-Felder mit
 * Default) erhöhen die Minor-Version, Signaturänderungen die Major-Version.
 */
final class Contracts
{
    public const string VERSION = '1.5.0';
}
