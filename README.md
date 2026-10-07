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
| `Documents` | `IncomingDocuments` | Posteingang-Ingest für Module (z. B. ein Pull von Paperless-NGX): `ingest()` legt den Beleg in der Kern-Ablage an und feuert `DocumentStored`, eine Hash-Dublette (`sha256`) liefert den bestehenden Beleg mit `duplicate = true` zurück, statt einen neuen anzulegen; `find()` liefert den Verweis auf einen Beleg anhand seiner ID, oder `null`. |
| `Mail` | `MailOutbox` | Versand im Branding-Layout, Eintrag im Postausgang, feuert `MailSent`. |
| `Assets` | `CustomerAssets` | Lesen/Anlegen/Kündigen von Kundenobjekten; `create()`/`cancel()` feuern `CustomerAssetCreated`/`CustomerAssetCancelled`; `findByLabel()` liefert aktive Objekte zuerst. Ein Objekt hat entweder eine Quelle (`source`/`externalId`, z. B. `"domainrobot"`) oder einen Freitext-Anbieter (`providerName`, z. B. `"IONOS"`); `findBySource()` findet ein Objekt anhand seiner externen Kennung, `attachSource()` ordnet ein bisher manuelles Objekt nachträglich einer Quelle zu, `updateFromSource()` übernimmt eine Connector-Rückmeldung (Status, Verlängerung, Autorenew, `attributes` per Merge), `forInvoiceItem()` liefert die Objekte einer Rechnungsposition. |
| `Assets` | `AssetSource` | Objekt-Vorschläge externer Quellen für den künftigen Objekt-Picker (Zielbild Phase 5) — kein Bezug zu bereits angelegten Kundenobjekten nötig. |
| `Contacts` | `Contacts` | Rein lesender Kontaktzugriff — Module legen/ändern keine Kontakte. |
| `Accounting` | `Journal` | Soll=Haben wird erzwungen (`UnbalancedEntryException`), festgeschriebene Perioden lehnen ab (`PeriodClosedException`). |
| `Invoices` | `Invoices` | `createDraft()` liefert immer eine Rechnung im Status Draft; Defaults (Bankkonto, Zahlungsziel, Steuerkategorie) ergänzt der Kern. Finalisierung/Versand/Storno bleiben Sache des Kern-UI. `findItem()` liefert den Verweis auf eine Rechnungsposition (inkl. Rechnungsnummer/-status und Kern-URL) anhand ihrer ID, oder `null`. `paidBetween()` liefert alle im Zeitraum bezahlten Ausgangsrechnungen als `PaidDocument` (`type = "invoice"`), z. B. für den Profit-Split. |
| `Invoices` | `InvoiceItemExtension` | Positions-Erweiterung einer Rechnung durch ein Modul (siehe Abschnitt „Positions-Erweiterung"). |
| `Documents` | `IncomingInvoices` | Rein lesender Zugriff auf Eingangsrechnungen. `paidBetween()` liefert alle im Zeitraum bezahlten Eingangsrechnungen als `PaidDocument` (`type = "incoming_invoice"`); `find()` liefert den Verweis auf eine Eingangsrechnung anhand ihrer ID, oder `null`. |
| `Documents` | `InvoiceExtractor` | KI-Extraktions-Provider für Eingangsrechnungen, Container-Tag `mytechio.invoice_extractors`. Der Kern wählt über eine Konfiguration genau einen Treiber aus; `extract()` liefert die rohe §7.6-Payload (Validierung macht der Kern) und wirft `ExtractionFailedException` bei Transportfehlern/blockierter Generierung. |
| `Articles` | `ArticleStatistics` | Lesender Zugriff auf die Artikel-BI-Auswertungen (Umsatz, Wareneinsatz, Rohertrag); der Kern implementiert diesen Vertrag über die Kern-Tabellen, ein Modul zeigt die Daten nur an. Die Array-Formen in `ArticleOverview`/`ArticleDetailStats` entsprechen exakt der Kern-Implementierung. |
| `Tax` | `VatIdChecker` | VIES-Prüfung einer USt-ID. Ohne aktives Modul bindet der Kern einen Null-Client, der stets `VatIdCheckUnavailableException` wirft (heutiger „VIES nicht erreichbar"-Pfad); `InvalidVatIdFormatException` bei formal ungültiger USt-ID. |
| `Settings` | `ModuleSettings` | Modulspezifische Einstellungen; ENV/Config hat immer Vorrang vor der Datenbank (`isFromEnvironment()`). Implementierung erst Phase 3 — hier nur Interface + Fake. |
| `Connectors` | `Connector` | Einheitlicher Satz an Status-Abfragen/Aktionen für externe Registrare/Lizenzserver; `sync()` darf lange laufen und gehört in eine Queue. |
| `Modules` | `ModuleLifecycle` | Lebenszyklus-Hooks (`onInstall`/`onEnable`/`onDisable`/`onUninstall`), vom Kern über den Manifest-Schlüssel `lifecycle` aufgelöst; `onEnable()` darf werfen (Modul bleibt deaktiviert), `onDisable()` nicht. `AbstractModuleLifecycle` liefert leere Implementierungen zum Erben. |
| `Modules` | `HealthCheck` | Gesundheitsstatus (`HealthStatus`) für die Modulübersicht; der Kern ruft `health()` nur dort und mit Timeout-Schutz auf — keine langsamen Netzaufrufe in Implementierungen. |
| `Modules` | `ModuleState` | Laufzeit-Zustand eines Moduls für Hintergrund-Code (siehe Abschnitt „Modulzustand im Hintergrund"): `isActive()` prüft bekannt+aktiviert+kompatibel, `isEnabled()` nur den rohen Schalter. |

## Modul-Lebenszyklus und Gesundheit

Module können im Manifest zusätzlich eine `lifecycle`-Klasse angeben, die
der Kern per Container auflöst:

```json
{
    "lifecycle": "MyTechIO\\Domainrobot\\DomainrobotModule",
    "roles": {
        "superadmin": ["*"],
        "admin": ["*"],
        "sales": ["domainrobot.view"]
    }
}
```

- `lifecycle` (optional): FQCN einer Klasse, die `Modules\ModuleLifecycle`
  und/oder `Modules\HealthCheck` implementiert. Der Kern ruft die
  Lebenszyklus-Hooks beim Aktivieren/Deaktivieren auf (Reihenfolge beim
  ersten Aktivieren: Migrationen → `onInstall()` → `onEnable()`) und
  `health()` ausschließlich auf der Modulübersicht, mit Timeout-Schutz
  (Fehler ⇒ `HealthStatus::error()`).
- `roles` (optional): Standardzuordnung der Modul-Permissions zu
  Kern-Rollen (`superadmin`, `admin`, `accountant`, `sales`, `viewer`);
  `"*"` steht für alle Permissions des Moduls. Fehlt der Block, gilt das
  bisherige Verhalten (alle Permissions an `superadmin` + `admin`).
  Unbekannte Rollen führen zu einem Manifest-Fehler im Kern.

## Modulzustand im Hintergrund

Hintergrund-Code eines Moduls — Scheduler-Einträge, Listener auf Kern-Ereignisse,
queued Jobs — darf nur laufen, wenn das Modul **aktiv** ist, nicht nur aktiviert,
sondern auch kompatibel. Dafür bindet der Kern `Modules\ModuleState`, und
Hintergrund-Code prüft ihn als Erstes:

```php
// Scheduler-Eintrag, zusätzlich zu bestehenden Bedingungen
$schedule->job(new SyncDomainsJob)
    ->daily()
    ->when(fn () => app(ModuleState::class)->isActive('domainrobot'));

// Listener / Job
public function handle(): void
{
    if (! app(ModuleState::class)->isActive('domainrobot')) {
        return; // beendet sich still, kein Fehler
    }

    // ...
}
```

- `isActive()` ist die richtige Prüfung für Hintergrund-Code — sie berücksichtigt
  Kompatibilität, nicht nur den Schalter aus der Modultabelle.
- `isEnabled()` liefert ausschließlich diesen rohen Schalter; Hintergrund-Code
  sollte i. d. R. `isActive()` verwenden, nicht `isEnabled()`.
- Synchron aufgerufene Vertragsimplementierungen (`Connector`, `CustomerAssets`
  usw.) prüfen `ModuleState` NICHT selbst — der Kern filtert seine Registries
  bereits nach aktiven Modulen, bevor er eine Implementierung aufruft.
- `Fakes\FakeModuleState` ist in Modul-Tests standardmäßig für JEDES Modul
  aktiv — Tests sollen nicht unbeabsichtigt stillstehen, nur weil ein
  Modulname nicht gesät wurde. `activate()`/`deactivate()` schalten ein
  einzelnes Modul für die Dauer eines Tests um.

## Deinstallation

`php artisan module:uninstall <name>` (Kern) ruft zuerst
`ModuleLifecycle::onUninstall()` des Moduls auf und entfernt danach die
Kern-Buchhaltung des Moduls: die `modules`-Zeile, `module_settings` und die
Modul-Permissions (aus allen Rollen und aus der Permission-Tabelle).

**Modultabellen bleiben stehen** — GoBD/Nachvollziehbarkeit verlangen, dass
einmal erfasste Daten (Domains, Kundenobjekte, Dokument-Referenzen, …) nicht
durch eine Deinstallation verschwinden. `onUninstall()` darf deshalb **nur**
technische Caches/Zustände räumen (z. B. Health-Cache, zwischengespeicherte
Tokens) — niemals fachliche Modultabellen leeren oder droppen.

## Positions-Erweiterung

Module können eine Rechnungsposition um eigene Daten ergänzen, ohne dass der Kern
den fachlichen Inhalt kennen muss: `Invoices\InvoiceItemExtension` legt seinen
Teil unter `extras[key()]` einer Position ab. Implementierungen werden über den
Container-Tag `mytechio.invoice_item_extensions` gesammelt (analog zu
`ConnectorRegistry`):

```php
app()->tag(InvoiceItemAssetsExtension::class, 'mytechio.invoice_item_extensions');
```

- `key()` **muss** dem Modulnamen entsprechen — der Kern filtert Erweiterungen
  deaktivierter Module über diesen Schlüssel heraus.
- `validate()` prüft den eigenen Teil der `extras` (z. B. ob verknüpfte IDs
  existieren und zum Kontakt gehören); Fehler landen im Kern unter
  `items.{i}.extras.{key}.{feld}`.
- `afterItemsSynced()` läuft nach dem Neuanlegen aller Positionen einer
  Rechnung, innerhalb der Kern-Transaktion — hier löst/erzeugt ein Modul z. B.
  seine eigenen Verknüpfungen.
- `annotate()` liefert Zusatzzeilen für den Beleg (PDF), die der Kern unter der
  Positionsbeschreibung einfügt.
- `duplicate()` liefert den eigenen Teil der `extras` für eine Belegkopie
  (Duplizieren/Storno) — i. d. R. ohne Verknüpfungen zum Original.

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
- `FakeInvoices` — merkt sich Entwürfe, liefert immer Status `draft`; `seedItem()` für `findItem()`, `seedPaidDocument()` für `paidBetween()` (sortiert, auf den Zeitraum gefiltert).
- `FakeModuleSettings` — `markFromEnvironment()` simuliert ENV-Vorrang.
- `FakeConnector` — konfigurierbarer Schlüssel/Typen/Status/Aktionen/Ereignisse.
- `FakeModuleLifecycle` — zählt Aufrufe je Hook, `failOnEnable()` simuliert einen fehlschlagenden `onEnable()`.
- `FakeHealthCheck` — liefert einen vorgegebenen `HealthStatus`, `withStatus()` zum Umkonfigurieren.
- `FakeInvoiceItemExtension` — zeichnet jeden Aufruf auf (`validateCalls`, `afterItemsSyncedCalls`, `annotateCalls`, `duplicateCalls`), `withValidationErrors()`/`withAnnotationLines()` zum Umkonfigurieren.
- `FakeInvoiceExtractor` — liefert eine vorgegebene Payload (`returns()`) oder wirft eine vorgegebene `ExtractionFailedException` (`throws()`), zählt jeden Aufruf (`calls()`), `withConfigured()` zum Umschalten.
- `FakeArticleStatistics` — liefert eine leere Auswertung, solange nichts gesät wurde; `seedOverview()`/`seedArticle()` legen die Ergebnisse fest.
- `FakeIncomingInvoices` — `seed()` für `find()`, `seedPaidDocument()` für `paidBetween()` (sortiert, auf den Zeitraum gefiltert).
- `FakeVatIdChecker` — Ergebnis je USt-ID konfigurierbar (`seedResult()`), `unavailable()` schaltet auf „nicht erreichbar" (wirft `VatIdCheckUnavailableException`), `calls()` für Assertions.
- `FakeModuleState` — standardmäßig ist jedes Modul aktiv; `activate()`/`deactivate()` schalten ein Modul für den Test um.

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
