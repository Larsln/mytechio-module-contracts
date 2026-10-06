# mytechio/module-contracts

Verträge (Interfaces, DTOs, Fakes) zwischen dem **MyTechIO-Admin-Kern** und seinen
Modulen. Reines PHP-Paket ohne Framework- und ohne Money-Abhängigkeit: Geldbeträge
sind Strings mit 4 Nachkommastellen, Daten `Y-m-d`, Zeitstempel ISO-8601 — genau wie
bei den Domain-Ereignissen des Kerns.

## Zweck

Module (z. B. [`mytechio/module-domainrobot`](https://github.com/Larsln/mytechio-module-domainrobot))
brauchen Zugriff auf Kern-Funktionalität — Dokumentablage, Mailversand, Kundenobjekte,
Kontakte, Buchungen, Rechnungs-Entwürfe —, ohne den Kern selbst als Composer-Abhängigkeit
zu benötigen. Dieses Paket definiert genau diese Schnittstelle: Der Kern bindet
Implementierungen gegen die Interfaces, Module injizieren nur die Interfaces. Zusätzlich
liefert das Paket zu jedem Vertrag einen `Fake*` für Modul-Tests, damit Module ihre
Suite ohne den Kern laufen lassen können.

## Installation

```bash
composer require mytechio/module-contracts
```

## Verträge

| Namespace | Interface | Kern-Semantik |
|---|---|---|
| `Documents` | `DocumentStore` | Lokale GoBD-Ablage ist Besitzer, Paperless-NGX asynchroner Zweit-Viewer; feuert `DocumentStored`. |
| `Mail` | `MailOutbox` | Versand im Branding-Layout, Eintrag im Postausgang, feuert `MailSent`. |
| `Assets` | `CustomerAssets` | Lesen/Anlegen/Kündigen von Kundenobjekten; `create()`/`cancel()` feuern `CustomerAssetCreated`/`CustomerAssetCancelled`; `findByLabel()` liefert aktive Objekte zuerst. |
| `Assets` | `AssetSource` | Objekt-Vorschläge externer Quellen für den künftigen Objekt-Picker (Zielbild Phase 5) — kein Bezug zu bereits angelegten Kundenobjekten nötig. |
| `Contacts` | `Contacts` | Rein lesender Kontaktzugriff — Module legen/ändern keine Kontakte. |
| `Accounting` | `Journal` | Soll=Haben wird erzwungen (`UnbalancedEntryException`), festgeschriebene Perioden lehnen ab (`PeriodClosedException`). |
| `Invoices` | `Invoices` | `createDraft()` liefert immer eine Rechnung im Status Draft; Defaults (Bankkonto, Zahlungsziel, Steuerkategorie) ergänzt der Kern. Finalisierung/Versand/Storno bleiben Sache des Kern-UI. |
| `Settings` | `ModuleSettings` | Modulspezifische Einstellungen; ENV/Config hat immer Vorrang vor der Datenbank (`isFromEnvironment()`). Implementierung erst Phase 3 — hier nur Interface + Fake. |
| `Connectors` | `Connector` | Einheitlicher Satz an Status-Abfragen/Aktionen für externe Registrare/Lizenzserver; `sync()` darf lange laufen und gehört in eine Queue. |

Jedes Interface ist im Quellcode mit deutschem PHPDoc dokumentiert, das Verhalten und
Kern-Semantik im Detail beschreibt — das ist die primäre Doku für Modulautoren.

Alle DTOs sind `final readonly` mit Konstruktor-Promotion und einer `toArray()`-Methode
(snake_case-Schlüssel). `MyTechIO\Contracts\ContractException` ist die gemeinsame
Basisklasse aller Vertrags-Ausnahmen.

## Fakes in Modul-Tests

Jeder Vertrag hat unter `MyTechIO\Contracts\Fakes\*` ein In-Memory-Test-Double, das
Module in ihrer eigenen Testbench binden, ohne den Kern zu benötigen:

```php
use MyTechIO\Contracts\Assets\CustomerAssets;
use MyTechIO\Contracts\Fakes\FakeCustomerAssets;

// z. B. in der TestCase::setUp() eines Moduls
$this->app->instance(CustomerAssets::class, new FakeCustomerAssets);
```

- `FakeDocumentStore` — liefert deterministische Pfade/Hashes, `stored()` für Assertions.
- `FakeMailOutbox` — `sent()`/`sentTo(email)` ohne PHPUnit-Abhängigkeit.
- `FakeCustomerAssets` — In-Memory mit Auto-IDs, `seed()` für den Ausgangszustand.
- `FakeContacts` — `seed()`, `setCountries()`.
- `FakeJournal` — prüft Soll=Haben exakt über Integer-Arithmetik (keine Floats),
  `seedAccount()` für den Kontenplan.
- `FakeInvoices` — merkt sich Entwürfe, liefert immer Status `draft`.
- `FakeModuleSettings` — `markFromEnvironment()` simuliert ENV-Vorrang.
- `FakeConnector` — konfigurierbarer Schlüssel/Typen/Status/Aktionen/Ereignisse.

## Versionierung

Semver: additive Änderungen (neue Methoden mit Default-Verhalten in den Fakes, neue
DTO-Felder mit Default) sind Minor-Releases; Signaturänderungen sind Major-Releases.
Kern und Module vergleichen sich über `MyTechIO\Contracts\Contracts::VERSION` gegen die
installierte Paketversion.

## Entwicklung

```bash
composer install
vendor/bin/pest --compact   # Tests
vendor/bin/pint             # Formatierung
```
